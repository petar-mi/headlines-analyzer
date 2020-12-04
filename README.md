# headlines-analyzer


<p>This app allows you to browse through a collection of news headlines from three Serbian news websites and see which words appear most frequently in them. <br/>
Websites, as well as a date range can be specified. If not, the whole collection is included in the analysis.<br/>
Each word will then be represented by the first image returned by Google image search.<br/>
User-specific analysis tool will also offer headline suggestions based on previous headline links visits.</p>
<p>Collection of news headlines itself is updated daily using server-side cron job which is scheduled to run a php script daily, which then visits 
selected websites, checks for any headlines not already stored in a database and adds them to the collection.</p>
<p>Preview version of the app that at the moment lacks registering new users & daily cron job functionalities is available at: <br/>
http://headlines-analyzer.herokuapp.com/ (Login credentials: test@mail.com : novasifra28) </p>
