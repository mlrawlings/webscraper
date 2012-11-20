var jsdom = require('jsdom');
var http = require('http');
var url = require('url');
var jquery = require('fs').readFileSync('jquery.js').toString();

// add a bit of sugar for our comparison statements
Number.prototype.between = function(min, max) {
	return this >= min && this < max;
}

// any page should work here
var pageUrl = 'http://cs.roanoke.edu/menu.html';

// object to store data
var linkData = {
	total: 0,
	working: 0,
	broken: []
};

// use jsdom + jquery to parse the html
jsdom.env({
	html: pageUrl,
	src: [jquery],
	done: function(err, window) {
		// this window is equivalent to the window variable
		// in your browser.  we set window.$ to $ for convenience.
		var $ = window.$;

		// get every link with an href attribute
		$('a[href]').each(function() {
			// increment the total link count
			linkData.total++;

			// get the link URL, resolved relative to the current page
			var linkHref = $(this).attr('href');
			var linkUrl = url.resolve(pageUrl, linkHref);

			// make a request for the link url to test
			makeRequest(linkUrl, linkHref);
		});
	}
});

var makeRequest = function(linkUrl, originalUrl) {
	// make an http GET request
	var request = http.get(linkUrl, function( response ) {
		// for informational and success messages
		if(response.statusCode.between(100, 300)) {
			// link is considered working
			// increment the working count
			linkData.working++;
		}
		// for redirect messages
		else if(response.statusCode.between(300, 400) && response.headers.location) {
			// make a request for the redirection
			makeRequest(response.headers.location, originalUrl || linkUrl);
		}
		// otherwise there was a client or server error (400 or 500 class)
		else {
			// store the broken url
			// use the originalUrl if available otherwise use the linkUrl
			linkData.broken.push(originalUrl || linkUrl);
		}

		if(linkData.total == linkData.working + linkData.broken.length) {
			// log information to the server console
			console.log(linkData);
		}
	}).on('error', function(err) {
		//if an error occures
		console.log('problem: ' + err.message);
	});
}


// create an http server to send the precomputed linkData above to the browser
http.createServer(function( request, response ) {
	// write to the http response header
	response.writeHead(200, {'Content-type': 'text/html'});

	// show how many links are working out of how many total links
	response.write('<h1>'+linkData.working+'/'+linkData.total+' links on this page are working.</h1>');
	
	// if there are broken links...
	if(linkData.broken.length) {
		// list the broken links
		response.write('<h2>The following links are broken:</h2>');
		response.write('<ul>');
		for(var i = 0; i < linkData.broken.length; i++) {
			response.write('<li>'+linkData.broken[i]+'</li>');
		}
		response.write('</ul>');
	}
	
	// end the response
	response.end();
}).listen(8101); // listen on port 8101