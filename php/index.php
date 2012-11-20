<?php
/*
	Require phpQuery.  Require is exactly like include except that
	it will throw an exception if it is unable to include the file.
	http://code.google.com/p/phpquery/
*/
require('phpQuery.php');

/* URL for the CPSC course descriptions */
$url = 'http://roanoke.edu/Academics/Academic_Departments/Math_CS_and_Physics/Computer_Science/Course_Descriptions.htm';

/* URL for the Stat course descriptions */
//$url = 'http://roanoke.edu/Academics/Academic_Departments/Math_CS_and_Physics/Statistics/Course_Descriptions_-_Stat.htm';

/* URL for the Physics course descriptions -- this does not work
   You could try to get this one working without breaking the other
   two URLs as an exersize on your own time */
//$url = 'http://roanoke.edu/Academics/Academic_Departments/Math_CS_and_Physics/Physics/Course_Descriptions.htm';


/* 
	allow roanoke.edu, see docs for info on allowing cross domain requests:
	http://code.google.com/p/phpquery/wiki/Ajax#Cross_Domain_Ajax
*/
phpQuery::ajaxAllowHost('roanoke.edu');

/*
	make a GET request for the url, passing the parsePage function as a callback
	more on callbacks: http://code.google.com/p/phpquery/wiki/Callbacks
*/
phpQuery::get($url, new Callback('parsePage', new CallbackParam));

/*
	callback which receives the html from the above request
*/
function parsePage($html) {
	// set up the html document, this exposes the pq variable
	phpQuery::newDocumentHTML($html);

	// we want to get all courses. each course is inside a p tag
	$paragraphs = pq('#main-content .generic-content-block p');

	// array to store the courses in
	$courses = [];

	// loop over each paragraph tag
	foreach($paragraphs as $p) {
		// declare the course as an (assoc) array
		$course = [];


		/*
			so the format of the course is:
		 	<strong>TITLE</strong><br>DESCRIPTION<br>HOURS<br><em>PREREQUISITE</em>
		*/

		
		// the course id and name is in a strong tag
		$title = pq($p)->find('strong')->text();

		// use regex to match ids (comma separated digits) and name
		// the result is stored in $title_matches. you could print_r
		// this variable to see what the result looks like.
		preg_match('/^([\d\s\,]+)\s(.*)/', $title, $title_matches);

		// convert the comma separated id list to an array
		$course['ids'] = preg_split('/\,\s?/', $title_matches[1]);

		// store the name
		$course['name'] = $title_matches[2];

		// split the innerHTML of the by <br> tags
		$text = explode('<br>', pq($p)->html());

		// the description is the second element. remove any html tags.
		$course['description'] = preg_replace('/\<.*?\>/', '', $text[1]);

		// if there is a third element...
		if(isset($text[2])) {
			// use regex to pull out the hours of lecture and lab
			preg_match('/Lecture\:\s*(\d+)\s*hrs\/w(?:ee)?k\.?(; Laboratory: (\d+)\s*hrs\/w(?:ee)?k\.)?/', $text[2], $hours_match);
			if(isset($hours_match[1])) {
				$course['lecture_hours'] = $hours_match[1];
			}
			if(isset($hours_match[3])) {
				$course['lab_hours'] = $hours_match[3];
			}
		}

		// prerequisites are listed inside an em tag
		$prereq = pq($p)->find('em')->html();

		// double check that this em tag is actually a prerequisite
		if( preg_match( '/^Prerequisite/', $prereq ) ) {
			// remove the Prerequite(s) prefix
			$prereq = preg_replace( '/Prerequisite(s)?\:/', '', $prereq );
			
			// match course listings in the prerequisite text
			preg_match_all( '/[A-Z]\w*\s*\w*\s+[\d\s\,]+\d/', $prereq, $prereq_matches );
			$required_courses = $prereq_matches[0];
			
			if( isset( $required_courses[0] ) ) {
				// create an array of prerequsite courses
				$course['prerequisites'] = [];
				foreach($required_courses as $c) {
					if( strpos( $c, ',' ) ) {
						// if there is a comma delimited course number list
						// eg. Mathematics 102, 201
						// grab the section name (Mathematics in the example above)
						preg_match('/^(.*?)\s+\d/', $c, $section_match);
						$section_name = $section_match[1];

						// split the course list by comma
						$course_array = preg_split( '/\,\s+/', $c );

						// the first item already has the section name
						$course['prerequisites'][] = $course_array[0];

						// add the section name to the remaining course numbers
						for($i = 1; $i < count($course_array); $i++) {
							$course['prerequisites'][] = $section_name . ' ' . $course_array[$i];
						}
					} else {
						// if there is no comma delimited list, just add the course
						$course['prerequisites'][] = $c;
					}
				}
			}
			else {
				// if no courses were found, just add the prerequisite text
				$course['prerequisites'] = $prereq;
			}
		}

		// add the course to the courses array
		$courses[] = $course;
	}

	// print out the courses.  use view-source in your browser to view this with the line breaks
	print_r($courses);

}