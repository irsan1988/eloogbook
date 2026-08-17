<?php
/**
 * Minimal self-contained QR Code Model 2 encoder for AVSEC login credentials.
 * Fixed to Version 4, error-correction level L. Payload capacity is sufficient
 * for the AVSEC-LOGIN:<32 hex> token and keeps hosting dependency-free.
 */
final class AvsecQrCode {
    private const VERSION = 4;
    private const SIZE = 33;               // 4 * version + 17
    private const DATA_CODEWORDS = 80;     // Version 4-L
    private const ECC_CODEWORDS = 20;      // Version 4-L, one RS block
    private const MASK = 0;

    public static function svg(string $text, int $scale=7, int $border=4): string {
        $matrix=self::matrix($text);
        $size=self::SIZE;
        $border=max(4,$border);
        $dim=$size+$border*2;
        $px=max(1,$scale)*$dim;
        $path='';
        for($y=0;$y<$size;$y++){
            for($x=0;$x<$size;$x++){
                if($matrix[$y][$x]){
                    $xx=$x+$border; $yy=$y+$border;
                    $path.='M'.$xx.' '.$yy.'h1v1h-1z';
                }
            }
        }
        return '<svg class="qr-svg" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="QR Code login Petugas" viewBox="0 0 '.$dim.' '.$dim.'" width="'.$px.'" height="'.$px.'" shape-rendering="crispEdges"><rect width="100%" height="100%" fill="#fff"/><path d="'.$path.'" fill="#000"/></svg>';
    }

    public static function matrix(string $text): array {
        $bytes=array_values(unpack('C*',$text) ?: []);
        if(count($bytes)>78) throw new InvalidArgumentException('Data QR Code terlalu panjang.');

        $bits=[];
        self::appendBits($bits,0x4,4);                 // Byte mode
        self::appendBits($bits,count($bytes),8);      // Version 1-9 count field
        foreach($bytes as $b) self::appendBits($bits,$b,8);

        $capacity=self::DATA_CODEWORDS*8;
        $remaining=$capacity-count($bits);
        self::appendBits($bits,0,min(4,max(0,$remaining)));
        while(count($bits)%8!==0) $bits[]=0;

        $data=[];
        for($i=0;$i<count($bits);$i+=8){
            $v=0; for($j=0;$j<8;$j++) $v=($v<<1)|$bits[$i+$j];
            $data[]=$v;
        }
        $pad=[0xEC,0x11]; $p=0;
        while(count($data)<self::DATA_CODEWORDS){ $data[]=$pad[$p&1]; $p++; }

        $div=self::rsDivisor(self::ECC_CODEWORDS);
        $ecc=self::rsRemainder($data,$div);
        $all=array_merge($data,$ecc);
        $codeBits=[];
        foreach($all as $cw) self::appendBits($codeBits,$cw,8);

        $size=self::SIZE;
        $modules=array_fill(0,$size,array_fill(0,$size,false));
        $func=array_fill(0,$size,array_fill(0,$size,false));
        $set=function(int $x,int $y,bool $dark) use (&$modules,&$func,$size): void {
            if($x<0||$x>=$size||$y<0||$y>=$size) return;
            $modules[$y][$x]=$dark; $func[$y][$x]=true;
        };

        // Timing patterns are drawn first; finder patterns overwrite their corners.
        for($i=0;$i<$size;$i++){
            $set(6,$i,$i%2===0);
            $set($i,6,$i%2===0);
        }

        $finder=function(int $cx,int $cy) use ($set,$size): void {
            for($dy=-4;$dy<=4;$dy++) for($dx=-4;$dx<=4;$dx++){
                $x=$cx+$dx; $y=$cy+$dy;
                if($x<0||$x>=$size||$y<0||$y>=$size) continue;
                $dist=max(abs($dx),abs($dy));
                $set($x,$y,$dist!==2 && $dist!==4);
            }
        };
        $finder(3,3); $finder($size-4,3); $finder(3,$size-4);

        // Version 4 alignment centers are 6 and 26. Only (26,26) does not overlap a finder.
        $align=function(int $cx,int $cy) use ($set): void {
            for($dy=-2;$dy<=2;$dy++) for($dx=-2;$dx<=2;$dx++)
                $set($cx+$dx,$cy+$dy,max(abs($dx),abs($dy))!==1);
        };
        $align(26,26);

        self::drawFormat($modules,$func,self::MASK);

        $i=0;
        for($right=$size-1;$right>=1;$right-=2){
            if($right===6) $right=5;
            $upward=((($right+1)&2)===0);
            for($vert=0;$vert<$size;$vert++){
                $y=$upward ? $size-1-$vert : $vert;
                for($j=0;$j<2;$j++){
                    $x=$right-$j;
                    if($func[$y][$x]) continue;
                    $bit=$i<count($codeBits) ? $codeBits[$i] : 0; // remainder bits are zero
                    $i++;
                    if((($x+$y)&1)===0) $bit^=1; // mask pattern 0
                    $modules[$y][$x]=($bit===1);
                }
            }
        }
        return $modules;
    }

    private static function drawFormat(array &$modules,array &$func,int $mask): void {
        $size=self::SIZE;
        $data=(1<<3)|$mask; // ECC level L = format bits 01
        $rem=$data;
        for($i=0;$i<10;$i++) $rem=($rem<<1)^((($rem>>9)&1)!==0 ? 0x537 : 0);
        $bits=(($data<<10)|$rem)^0x5412;
        $set=function(int $x,int $y,bool $dark) use (&$modules,&$func): void { $modules[$y][$x]=$dark; $func[$y][$x]=true; };

        for($i=0;$i<=5;$i++) $set(8,$i,(($bits>>$i)&1)!==0);
        $set(8,7,(($bits>>6)&1)!==0);
        $set(8,8,(($bits>>7)&1)!==0);
        $set(7,8,(($bits>>8)&1)!==0);
        for($i=9;$i<15;$i++) $set(14-$i,8,(($bits>>$i)&1)!==0);

        for($i=0;$i<8;$i++) $set($size-1-$i,8,(($bits>>$i)&1)!==0);
        for($i=8;$i<15;$i++) $set(8,$size-15+$i,(($bits>>$i)&1)!==0);
        $set(8,$size-8,true); // fixed dark module
    }

    private static function appendBits(array &$bits,int $value,int $length): void {
        for($i=$length-1;$i>=0;$i--) $bits[]=($value>>$i)&1;
    }

    private static function gfMultiply(int $x,int $y): int {
        $z=0;
        for($i=7;$i>=0;$i--){
            $z=($z<<1)^((($z>>7)&1)!==0 ? 0x11D : 0);
            if((($y>>$i)&1)!==0) $z^=$x;
        }
        return $z & 0xFF;
    }

    private static function rsDivisor(int $degree): array {
        $result=array_fill(0,$degree,0); $result[$degree-1]=1; $root=1;
        for($i=0;$i<$degree;$i++){
            for($j=0;$j<$degree;$j++){
                $result[$j]=self::gfMultiply($result[$j],$root);
                if($j+1<$degree) $result[$j]^=$result[$j+1];
            }
            $root=self::gfMultiply($root,0x02);
        }
        return $result;
    }

    private static function rsRemainder(array $data,array $divisor): array {
        $result=array_fill(0,count($divisor),0);
        foreach($data as $b){
            $factor=$b^$result[0];
            array_shift($result); $result[]=0;
            foreach($result as $i=>$v) $result[$i]=$v^self::gfMultiply($divisor[$i],$factor);
        }
        return $result;
    }
}
