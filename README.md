# Webscraper Examples
These are examples from my [web scraper presentation].
   
   [web scraper presentation]: https://speakerdeck.com/mlrawlings/web-scraping

## Node.js

### Set up
run `npm install` in the directory

### Getting jsdom to install on Windows
jsdom claims that contextify (a binary module) is optional, but it isn't.  To get contextify to build properly, download and install [Python 2.7.3] and make sure that C:/Python27 (or wherever you install it) is in your PATH (you may also need to make sure that your PYTHONPATH is correct).  Also download and install [Visual C++ 2010 Express] don't do 2012 - it won't work.

   [Python 2.7.3]: http://www.python.org/download/releases/2.7.3/
   [Visual C++ 2010 Express]: http://www.microsoft.com/visualstudio/eng/downloads

## PHP

[phpQuery] is a dependency and it is included in this repo.  The [Zend Framework] is another dependency you need to download it and add its location on your computer to the include_path in php.ini. 

  [phpQuery]: http://code.google.com/p/phpquery/
  [Zend Framework]: http://www.zend.com/en/community/downloads