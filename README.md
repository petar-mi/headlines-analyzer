# headlines-analyzer


<p>This app allows you to browse through a collection of news headlines from three Serbian news websites and see which words appear most frequently in them. <br/>
Websites, as well as a date range can be specified. If not, the whole collection is included in the analysis.<br/>
Each word will then be represented by the first image returned by Google image search.<br/>
User-specific analysis tool will also offer headline suggestions based on previous headline links visits.</p>
<p>Collection of news headlines itself is updated daily using server-side cron job which is scheduled to run a php script daily, which then visits 
selected websites, checks for any headlines not already stored in a database and adds them to the collection.</p>

<p>The app is written mainly in php and uses twig as a template engine which integrates php with html and JS for frontend rendering, as well as pepipostAPI to automate sending e-mails. MariaDb is used (a XAMPP component) as a database when running locally, while in production on heroku postgreSQL is being used. Additionally, cron is used as a linux system time-based job scheduler, set to run on a daily basis, crawl and scrap listed websites and update the database. </p>
  
<p>Preview version of the app that at the moment lacks registering new users & daily cron job functionalities is available at: <br/>
http://headlines-analyzer.herokuapp.com/ (Login credentials: test@mail.com : novasifra28) </p>
