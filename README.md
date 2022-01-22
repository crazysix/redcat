# Coding Challenge

Run program:
1. Clone the repo to the local machine.
2. Go to the cloned directory.
3. Run `php -S localhost:8000`.
4. Go to localhost:8000 in a browser.

Instructions:
1. Start at homepage (/).
2. Cick the link to upload a file. The sample file (/files/example.csv) can be used to upload and test.
3. After file upload, go the the data view.
4. Once columns are added, choose between the original data or the altered data with additional columns.

TODOs:
1. Add JS validation to forms.
2. Make it pretty.
3. Catch more edge cases.
4. Add unit testing.
5. Reduce method complexity in some classes.

This is an over-enginieered but fun app to view and manipulate CSV data. Enjoy!

## Coding Exercise Instructions
For this exercise, please do not use any PHP frameworks or Docker.

Write a PHP web application that can do the following:
1. Allow the user to upload a csv file. Assume the csv file has column headers in the first row.
2. Display the uploaded csv as a table in the browser (you may want to use Bootstrap DataTables).
3. Allow the user to define new columns in the table based on applying operations to existing columns.
	1. To add a new column, the user provides a column name and a formula which can consist of operations on existing columns (example below).
	2. To simplify the scope of this exercise, assume all the data in the csv are either numeric or string (i.e., don't worry about parsing text formatting), and the only operations you need to handle are single or multiple arithmetic (+ - * /) and concatenate (&). Assume column headers in the csv have no spaces; and require that user-defined column names have no spaces.
4. Example of how this works:
	1. User uploads a csv with the following contents. Display the contents as a table in the browser:
		city,product,units,price
		San Diego,apple,2,5
		Austin,apple,5,5
		Seattle,apple,6,5
		Portland,orange,none,10
		New York,orange,8,10
	2. User defines a new column, with column name "sales", and the following formula:
		* units * price
	3. Display updated table in the browser, calculating the contents of the new column according to the formula provided. If the operation is invalid for a row (e.g., applying arithmetic operator to non-numeric value) display NA.
		city,product,units,price,sales
		San Diego,apple,2,5,10
		Austin,apple,5,5,25
		Seattle,apple,6,5,30
		Portland,orange,none,10,NA
		New York,orange,8,10,80
	4. User defines a new column, with column name "city_sales", and the following formula (& represents concatenate operator):
		* product & " sales in " & city & " were " & sales
	5. Display updated table in the browser:
		city,product,units,price,sales,city_sales
		San Diego,apple,2,5,10,apple sales in San Diego were 10
		Austin,apple,5,5,25,apple sales in Austin were 25
		Seattle,apple,6,5,30,apple sales in Seattle were 30
		Portland,orange,none,10,NA,orange sales in Portland were NA
		New York,orange,8,10,80,orange sales in New York were 80
	6. Make sure the application fails elegantly, e.g. helpful error messages if the formula is invalid.
