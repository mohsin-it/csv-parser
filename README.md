# CSV Parser 

Parses CSV file and provide unique combinations in CSV or JSON format

### Executing program

* From the root directory run the below command in terminal
```
php parser.php --file=products_comma_separated.csv --unique-combinations=output.csv
```
* For JSON output
```
php parser.php --file=products_comma_separated.csv --unique-combinations=output.json
```

### Note
* File name passed to --file must be present and first row must be headings
* This CSV parser works with any number of headers in any order
* Unique combinations can also be store in JSON format. Pass .json file name in --unique-combinations
* This is just demo version. This can be extended to more advanced version
