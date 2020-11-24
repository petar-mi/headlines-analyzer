<?php

namespace App;

// App configuration

class Config 
{ // params for connecting to mvc base from basic course
    // const DB_HOST = 'localhost',
    //       DB_NAME = 'mvc',
    //       DB_USER = 'root',
    //       DB_PASSWORD = '',
    //       SHOW_ERRORS = true; // set to true for development, for production should be false so that errors are not shown in detail but saved to log file

    // params for connecting to local mysql base with a name mvclogin
          // const DB_HOST = 'localhost',
          // DB_NAME = 'mvclogin',
          // DB_USER = 'mvcuser',
          // DB_PASSWORD = 'novasifra',
          // SHOW_ERRORS = true, 

          // Data for Postgres SQL (Heroku add-on) - credentials periodically change!        
          const DB_HOST = 'ec2-52-31-233-101.eu-west-1.compute.amazonaws.com',
          DB_NAME = 'd2cpciup9a1td3',
          DB_USER = 'hchczruigipqmp',
          DB_PASSWORD = 'd21ed7a08e9b9518f5ca8ee6a7c41c0fc9cbfeaff9144ee4ae95eb18a250671d',
          SHOW_ERRORS = true, 

          SECRET_KEY = 'mK8yRfln1lE2IzYFjPp0Eql9BEZfTcZ9', // random key generated on https://randomkeygen.com/  (taken from CodeIgniter Encryption keys). This key is consumed in Token.php for token hashing
          PEPIPOST_API_KEY = '2cd01a8ca0bcc42107c74eb87972b1ff', // for automated email sending apikey from panel here  https://app.pepipost.com/index.php/    login: bobanabg@mail.com pass: Novasifra123
          PEPIPOST_DOMAIN = 'info@pepisandbox.com', // pepisandbox.com is the important part, info has been written arbitrarily
          // WEBSITES_TO_SCRAP = array("mondo" => "mondo.rs", "luftika" => "luftika.rs", "noizz" => "noizz.rs");
          // to be used locally:
          // WEBSITES_TO_SCRAP = array("mondo" => ["mondo.rs", "/opt/lampp/htdocs/App/Images/mondo_logo.png"], "luftika" => ["luftika.rs", "/opt/lampp/htdocs/App/Images/luftika_logo.png"], "noizz" => ["noizz.rs", "/opt/lampp/htdocs/App/Images/noizz_logo.png"]);
          // to be used for heroku:
          WEBSITES_TO_SCRAP = array("mondo" => ["mondo.rs", "https://user-images.githubusercontent.com/47416193/100085499-657cc500-2e4c-11eb-8e35-a2d99e439014.png"], "luftika" => ["luftika.rs", "https://user-images.githubusercontent.com/47416193/100085410-4a11ba00-2e4c-11eb-8ff6-b843948700de.png"], "noizz" => ["noizz.rs", "https://user-images.githubusercontent.com/47416193/100085501-66155b80-2e4c-11eb-8abe-ff7966b1536b.png"]);
          
          const EXCLUDE_WORDS = array(
            "Da", "Ne", "Li", "Zar", "Neka", "Ala", "Dakle", "Samo", "Jedino", "Kao", "Evo", "Eto", "Eno",
            "Sigurno", "Naravno", "Zacelo", "Dakako", "Verovatno", "Valjda", "Možda", "Nipošto", "Uistinu",
            "Zaista", "Zbilja", "Upravo", "Baš", "Bar", "Međutim", "Medjutim", "Pak", "Inače", "Takodje", "Takođe", "Uostalom",
            "Itd", "Još", "Tek", "Već", "Čak", "Se", "Što", "Osim",
            "Jedan", "Dva", "Tri", "Četiri", "Pet", "Šest", "Sedam", "Osam", "Devet", "Deset", "Dvadeset",
            "Trideset", "Četrdeset", "Pedeset", "Šezdeset", "Sedamdeset", "Osamdeset", "Devedeset", "Sto",
            "Dvesto", "Tristo", "Četrsto", "Petsto", "Šesto", "Sedamsto", "Osamsto", "Devetsto", "Hiljadu",
            "Hiljada", "Milijon", "Milion", "Miliona", "Milijarda", "Milijardu", "Ii", "Iii", "Iv", "V", "Vv", "Vii", "Viii", "Ix", "X", "Xx",
            "Prvi", "Prvog", "Prva", "Prve", "Prvu", "Prvo", "Drugi", "Drugog", "Druga", "Druge", "Drugu", "Drugo", "Treći", "Trećeg", "Treća", "Treće", "Treću",
            "Četvrti", "Četvrta", "Četvrto", "Četvrtu", "Četvrte", "Četvrtog", "Peti", "Peta", "Peto", "Petog", "Pete", "Petu",
            "Šesti", "Šesta", "Šesto", "Šeste", "Šestu", "Šestog", "Sedmi", "Sedma", "Sedmo", "Sedme", "Sedmog", "Sedmu",
            "Osmi", "Osma", "Osmo", "Osmog", "Osme", "Osmu", "Deveti", "Deveta", "Deveto", "Devete", "Devetu", "Devetog",
            "Deseti", "Deseta", "Deseto", "Desete", "Desetu", "Desetog", "Dvadeseti", "Dvadeseta", "Dvadeseto", "Dvadesete", "Dvadesetu", "Dvadesetog",
            "Stoti", "Stota", "Stoto", "Stote", "Stotog", "Stotu", "Hiljaditi", "Hiljadita", "Hiljadito", "Hiljaditu", "Hiljaditog ",
            "Ovde", "Onde", "Ovamo", "Tamo", "Tek", "Toliko", "Juče", "Zato", "Stoga", "Zbog",
            "Pod", "Kod", "U", "Na", "O", "Po", "Niz", "Uz", "S", "Sa", "Iza", "Od", "Do", "Pred", "Nad", "Pored",
            "Izmedju", "Između", "Posle", "Pre", "Nakon", "Oko", "Kroz", "Zbog", "Usled", "Iz", "Radi", "Za", "K", "Ka", "Prema",
            "Ah", "Jao", "Haj", "Hoj", "A", "O", "Oj", "Uh", "Ajoj", "Iš",
            "I", "Pa", "Ali", "Da", "Dok", "Jer", "Ako", "Kada", "Pošto", "Kako", "Te", "Ni",
            "Niti", "No", "Nego", "Ili", "Iako", "Mada", "Premda",
            "Biti", "Hteti", "Jesam", "Budem", "Budemo", "Budeš", "Budete", "Bude", "Budu",
            "Bio", "Sam", "Si", "Je", "Smo", "Ste", "Su", "Bih", "Bi", "Bismo", "Biste", "Biše", "Biću", "Bićeš", "Biće", "Bićemo", "Bićete",
            "Bili", "Budi", "Budimo", "Budite", "Bila", "Bilo", "Bile", "Hoću", "Hoćeš", "Hoće", "Hoćemo", "Hoćete",
            "Ću", "Ćeš", "Će", "Ćemo", "Ćete", "Neću", "Nećeš", "Neće", "Nećemo", "Nećete", "Sam", "Si", "Smo", "Ste", "Su",
            "Hteću", "Htećeš", "Htećemo", "Htećete", "Hteće", "Hteo", "Hteli", "Jesi", "Jesmo", "Jeste", "Jesu",
            "Nisam", "Nisi", "Nije", "Nismo", "Niste", "Nisu",
            "Ja", "Ti", "Mi", "Vi", "Oni", "One", "Ona", "On", "Ono", "Sebi", "Sebe", "Sobom", "Ko", "Šta", "Neko", "Nešto", "Niko", "Ništa",
            "Svako", "Svašta", "Moj", "Moja", "Moje", "Moji", "Tvoj", "Tvoja", "Tvoje", "Tvoji", "Njegov", "Njegova", "Njegovo", "Njegovi", "Njegove",
            "Nas", "Nasa", "Nase", "Nasi", "Vaš", "Vaša", "Vaše", "Vaši", "Njihov", "Njihova", "Njihovi", "Njihove", "Njihovo",
            "Svoj", "Svoja", "Svoje", "Svoji", "Ovaj", "Ova", "Ovo", "Ove", "Ovi", "Ovoliki", "Ovolika", "Ovolike", "Ovoliko",
            "Ovakav", "Ovakva", "Ovakvo", "Ovakvi", "Taj", "Ta", "To", "Ti", "Te", "Ta", "Toliko", "Tolika", "Tolike", "Toliki",
            "Takav", "Takva", "Takvo", "Takvi", "Takve", "Onaj", "Oni", "Ono", "One", "Ona", "Onolika", "Onolike", "Onoliki", "Onolika",
            "Onakav", "Onakva", "Onakvi", "Onakve", "Onakvo", "Koji", "Koja", "Koje", "Koliki", "Kolika", "Koliko", "Kolike",
            "Kakav", "Kakva", "Kakvo", "Kakvi", "Kakve", "Čiji", "Čija", "Čije", "Neki", "Neka", "Neko", "Neke",
            "Nekakav", "Nekakva", "Nekakvo", "Nakakvi", "Nekakve", "Nekolik", "Nekoliko", "Nekolika", "Nekolike", "Nekoliki",
            "Nečiji", "Nečija", "Nečije", "Nikoja", "Nikoje", "Nikoji", "Nikakav", "Nikakva", "Nikakve", "Nikakvo", "Nikakvi",
            "Ničiji", "Ničija", "Ničije", "Svaki", "Svaka", "Svako", "Svake", "Svakakva", "Svakakve", "Svakakvi", "Svakakvo",
            "Svačiji", "Svačija", "Svačije", "Ma", "God",
            "Sve", "Ih", "Ga", "Sami", "Tebe", "Svi", "Svim", "Šta", "Koju", "Ju", "Me", "Sva", "Mu", "Može", "Lična", "Joj", "Vam", "Tu",
            "-", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "-", "+", "=", ",", ".", "?", "/", "'", "}", "{", "[", "]", "`", "~", ";", ":", "<", ">",
            "",
            "Video", "Foto", "Ima", "Bez", "Noizz", "Mondo", "Luftika"
         );
         const CHARS_TO_LOOK_FOR = array("/^š/", "/^đ/", "/^ž/", "/^č/", "/^ć/", "/(?<!^)Š/", "/(?<!^)Đ/", "/(?<!^)Ž/", "/(?<!^)Č/", "/(?<!^)Ć/");
         const CHARS_TO_CHANGE = array("Š", "Đ", "Ž", "Č", "Ć", "š", "đ", "ž", "č", "ć");
}