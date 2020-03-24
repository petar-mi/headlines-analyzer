<?php
namespace App;
//Author: Nikola Milosevic, http://www.inspiratron.org
// This work is done as part of master thesis of Nikola Milosevic on
//University of Belgrade, School of Electrical Engineering
//This program is free software: you can redistribute it and/or modify it
//under the terms of the GNU General Public License as published by the
//Free Software Foundation, either version 3 of the License or (at your
//option) any later version.
//This program is distributed in the hope that it will be useful, but
//WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
//General Public License for more details.
//You should have received a copy of the GNU General Public License along
//with this program. If not, see http://www.gnu.org/licenses/.
class Stemmer
{
    public static function stem($str)
    {
        header('Content-Type: text/plain; charset=UTF-8');
        $outtext = "";
        $rules = array(
            //Currently 285 rules
            'ovnicxki' => '',
            'ovnicxka' => '',
            'ovnika' => '',
            'ovniku' => '',
            'ovnicxe' => '',
            'kujemo' => '',
            'ovacyu' => '',
            'ivacyu' => '',
            'isacyu' => '',
            'dosmo' => '',
            'ujemo' => '',
            'ijemo' => '',
            'ovski' => '',
            'ajucxi' => '',
            'icizma' => '',
            'ovima' => '',
            'ovnik' => '',
            'ognu' => '',
            'inju' => '',
            'enju' => '',
            'cxicyu' => '',
            'sxtva' => '',
            'ivao' => '',
            'ivala' => '',
            'ivalo' => '',
            'skog' => '',
            'ucxit' => '',
            'ujesx' => '',
            'ucyesx' => '',
            'ocyesx' => '',
            'osmo' => '',
            'ovao' => '',
            'ovala' => '',
            'ovali' => '',
            'ismo' => '',
            'ujem' => '',
            'esmo' => '',
            'asmo' => '', //pravi gresku kod pevasmo
            'zxemo' => '',
            'cyemo' => '',
            'cyemo' => '',
            'bemo' => '',
            'ovan' => '',
            'ivan' => '',
            'isan' => '',
            'uvsxi' => '',
            'ivsxi' => '',
            'evsxi' => '',
            'avsxi' => '',
            'sxucyi' => '',
            'uste' => '',
            'icxe' => 'i', //bilo ik
            'acxe' => 'ak',
            'uzxe' => 'ug',
            'azxe' => 'ag', // mozda treba az, pokazati, pokazxe
            'aci' => 'ak',
            'oste' => '',
            'aca' => '',
            'enu' => '',
            'enom' => '',
            'enima' => '',
            'eta' => '',
            'etu' => '',
            'etom' => '',
            'adi' => '',
            'alja' => '',
            'nju' => 'nj',
            'lju' => '',
            'lja' => '',
            'lji' => '',
            'lje' => '',
            'ljom' => '',
            'ljama' => '',
            'zi' => 'g',
            'etima' => '',
            'ac' => '',
            'becyi' => 'beg',
            'nem' => '',
            'nesx' => '',
            'ne' => '',
            'nemo' => '',
            'nimo' => '',
            'nite' => '',
            'nete' => '',
            'nu' => '',
            'ce' => '',
            'ci' => '',
            'cu' => '',
            'ca' => '',
            'cem' => '',
            'cima' => '',
            'sxcyu' => 's',
            'ara' => 'r',
            'iste' => '',
            'este' => '',
            'aste' => '',
            'ujte' => '',
            'jete' => '',
            'jemo' => '',
            'jem' => '',
            'jesx' => '',
            'ijte' => '',
            'inje' => '',
            'anje' => '',
            'acxki' => '',
            'anje' => '',
            'inja' => '',
            'cima' => '',
            'alja' => '',
            'etu' => '',
            'nog' => '',
            'omu' => '',
            'emu' => '',
            'uju' => '',
            'iju' => '',
            'sko' => '',
            'eju' => '',
            'ahu' => '',
            'ucyu' => '',
            'icyu' => '',
            'ecyu' => '',
            'acyu' => '',
            'ocu' => '',
            'izi' => 'ig',
            'ici' => 'ik',
            'tko' => 'd',
            'tka' => 'd',
            'ast' => '',
            'tit' => '',
            'nusx' => '',
            'cyesx' => '',
            'cxno' => '',
            'cxni' => '',
            'cxna' => '',
            'uto' => '',
            'oro' => '',
            'eno' => '',
            'ano' => '',
            'umo' => '',
            'smo' => '',
            'imo' => '',
            'emo' => '',
            'ulo' => '',
            'sxlo' => '',
            'slo' => '',
            'ila' => '',
            'ilo' => '',
            'ski' => '',
            'ska' => '',
            'elo' => '',
            'njo' => '',
            'ovi' => '',
            'evi' => '',
            'uti' => '',
            'iti' => '',
            'eti' => '',
            'ati' => '',
            'vsxi' => '',
            'vsxi' => '',
            'ili' => '',
            'eli' => '',
            'ali' => '',
            'uji' => '',
            'nji' => '',
            'ucyi' => '',
            'sxcyi' => '',
            'ecyi' => '',
            'ucxi' => '',
            'oci' => '',
            'ove' => '',
            'eve' => '',
            'ute' => '',
            'ste' => '',
            'nte' => '',
            'kte' => '',
            'jte' => '',
            'ite' => '',
            'ete' => '',
            'cyi' => '',
            'usxe' => '',
            'esxe' => '',
            'asxe' => '',
            'une' => '',
            'ene' => '',
            'ule' => '',
            'ile' => '',
            'ele' => '',
            'ale' => '',
            'uke' => '',
            'tke' => '',
            'ske' => '',
            'uje' => '',
            'tje' => '',
            'ucye' => '',
            'sxcye' => '',
            'icye' => '',
            'ecye' => '',
            'ucxe' => '',
            'oce' => '',
            'ova' => '',
            'eva' => '',
            'ava' => 'av',
            'uta' => '',
            'ata' => '',
            'ena' => '',
            'ima' => '',
            'ama' => '',
            'ela' => '',
            'ala' => '',
            'aka' => '',
            'aja' => '',
            'jmo' => '',
            //'uga'=>'',
            'oga' => '',
            'ega' => '',
            'aća' => '',
            'oca' => '',
            'aba' => '',
            'cxki' => '',
            'ju' => '',
            'hu' => '',
            'cyu' => '',
            'cu' => '',
            'ut' => '',
            'it' => '',
            'et' => '',
            'at' => '',
            'usx' => '',
            'isx' => '',
            'esx' => '',
            'esx' => '',
            'uo' => '',
            'no' => '',
            'mo' => '',
            'mo' => '',
            'lo' => '',
            'ko' => '',
            'io' => '',
            'eo' => '',
            'ao' => '',
            'un' => '',
            'an' => '',
            'om' => '',
            'ni' => '',
            'im' => '',
            'em' => '',
            'uk' => '',
            'uj' => '',
            'oj' => '',
            'li' => '',
            'ci' => '',
            'uh' => '',
            'oh' => '',
            'ih' => '',
            'eh' => '',
            'ah' => '',
            'og' => '',
            'eg' => '',
            'te' => '',
            'sxe' => '',
            'le' => '',
            'ke' => '',
            'ko' => '',
            'ka' => '',
            'ti' => '',
            'he' => '',
            'cye' => '',
            'cxe' => '',
            'ad' => '',
            'ecy' => '',
            'ac' => '',
            'na' => '',
            'ma' => '',
            'ul' => '',
            'ku' => '',
            'la' => '',
            'nj' => 'nj',
            'lj' => 'lj',
            'ha' => '',
            'a' => '',
            'e' => '',
            'u' => '',
            'sx' => '',
            'o' => '',
            'i' => '',
            //'k'=>'',
            'j' => '',
            //'t'=>'',
            //'n'=>'', //London, londona
            'i' => ''
        );
        $dictionary = array(
            //biti glagol
            'bih' => 'biti',
            'bi' => 'biti',
            'bismo' => 'biti',
            'biste' => 'biti',
            'bisxe' => 'biti',
            'budem' => 'biti',
            'budesx' => 'biti',
            'bude' => 'biti',
            'budemo' => 'biti',
            'budete' => 'biti',
            'budu' => 'biti',
            'bio' => 'biti',
            'bila' => 'biti',

            'bili' => 'biti',
            'bile' => 'biti',
            'biti' => 'biti',
            'bijah' => 'biti',
            'bijasxe' => 'biti',
            'bijasmo' => 'biti',
            'bijaste' => 'biti',
            'bijahu' => 'biti',
            'besxe' => 'biti',
            //jesam
            'sam' => 'jesam',
            'si' => 'jesam',
            'je' => 'jesam',
            'smo' => 'jesam',
            'ste' => 'jesam',
            'su' => 'jesam',
            'jesam' => 'jesam',
            'jesi' => 'jesam',
            'jeste' => 'jesam',
            'jesmo' => 'jesam',
            'jeste' => 'jesam',
            'jesu' => 'jesam',
            // glagol hteti
            'cyu' => 'hteti',
            'cyesx' => 'hteti',
            'cye' => 'hteti',
            'cyemo' => 'hteti',
            'cyete' => 'hteti',
            'hocyu' => 'hteti',
            'hocyesx' => 'hteti',
            'hocye' => 'hteti',
            'hocyemo' => 'hteti',
            'hocyete' => 'hteti',
            'hocye' => 'hteti',
            'hteo' => 'hteti',
            'htela' => 'hteti',
            'hteli' => 'hteti',
            'htelo' => 'hteti',
            'htele' => 'hteti',
            'htedoh' => 'hteti',
            'htede' => 'hteti',
            'htede' => 'hteti',
            'htedosmo' => 'hteti',
            'htedoste' => 'hteti',
            'htedosxe' => 'hteti',
            'hteh' => 'hteti',
            'hteti' => 'hteti',
            'htejucyi' => 'hteti',
            'htevsxi' => 'hteti',
            // glagol moći
            'mogu' => 'mocyi',
            'možeš' => 'mocyi',
            'može' => 'mocyi',
            'možemo' => 'mocyi',
            'možete' => 'mocyi',
            'mogao' => 'mocyi',
            'mogli' => 'mocyi',
            'moći' => 'mocyi'
        );
        //if (isset($_POST["queue"])) {
            //$text = strtolower($_POST["queue"]);
            $text = strtolower($str);
            $text = trim($text);
            $substr1 = "";
            $substr2 = "";
            //Tokenizes and set interpucntion marks to be separated with blank

            for ($i = 0; $i < strlen($text); $i++) {
                if (($text[$i] == "." or $text[$i] == "," or $text[$i] == "!" or
                    $text[$i] == ":" or $text[$i] == "?" or $text[$i] == "(" or $text[$i] == ")" or
                    $text[$i] == ";") and $text[$i - 1] != " ") {
                    $substr1 = substr($text, 0, $i);
                    $substr2 = substr($text, $i, strlen($text));
                    $text = $substr1 . " " . $substr2;
                }
                if (($text[$i] == "." or $text[$i] == "," or $text[$i] == "!" or
                    $text[$i] == ":" or $text[$i] == "?" or $text[$i] == "(" or $text[$i] == ")" or
                    $text[$i] == ";") and $text[$i + 1] != " ") {
                    $substr1 = substr($text, 0, $i + 1);
                    $substr2 = substr($text, $i + 1, strlen($text));
                    $text = $substr1 . " " . $substr2;
                }
            }
            //Creates tokens
            $tokens = explode(" ", $text);
            $arrkeys = array_keys($rules);
            //Stemmes
            for ($i = 0; $i < count($tokens); $i++) {
                $currtoken = $tokens[$i];
                //Checks if word is in dictionary, if yes changes to --
                //original form
                if (in_array($currtoken, array_keys($dictionary))) {
                    $tokens[$i] = $dictionary[$currtoken];
                    $outtext = $outtext . " " . $tokens[$i];
                    continue;
                }
                for ($j = 0; $j < count($rules); $j++) {
                    if (preg_match('/\b(cx|cy|zx|dx|sx)/', $tokens[$i]))
                        $pattern = '/(\w{3,})' . $arrkeys[$j] . '\b/';
                    else
                        $pattern = '/(\w{2,})' . $arrkeys[$j] . '\b/';
                    $arrkey = $arrkeys[$j];
                    //'/(\w{2,})ski\b/'

                    if (preg_match($pattern, $tokens[$i])) {
                        $tokens[$i] =
                            preg_replace($pattern, '$1' . $rules[$arrkey], $tokens[$i]);
                        break;
                    }
                }
                $outtext = $outtext . " " . $tokens[$i];
            }
        // } else {
        //     $text = "Error: Not set queue";
        //     $outtext = $text;
        // }
        //Output
        //echo $outtext;
        return $outtext;
    }
}
