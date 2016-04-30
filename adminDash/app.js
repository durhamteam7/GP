var adminApp = angular.module('adminDash', ['utilities','serverComms','rzModule', 'ui.bootstrap', 'googlechart', "checklist-model", 'datetimepicker', 'toggle-switch', 'ngAutocomplete', 'bw.paging']);

var mammalwebBaseURL = "http://www.mammalweb.org/biodivimages/"; //root of all img URLs


/** Main Controller for the admin app. Stores and manipulates data from mammalweb db
 * @memberof adminApp
 * @ngdoc controller
 * @name MainController
 * @param $scope {service} controller scope
 * @param ajax {factory} ajax methods for server
 */
adminApp.controller('MainController', ['$scope', 'ajax', function($scope, serverComm) {
    /**
     * @memberof MainController
     * @property results
     * @type array
     * @description contains an array of photo/sequence objects matching the query on a page.
     */
    $scope.results = [];

    /**
     * @memberof MainController
     * @property options
     * @description Array of key value pairs
     * @type array
     */
    $scope.options = {};

    /**
     * @memberof MainController
     * @property fullResults
     * @description Array of every result matched by the query
     * @type array
     */
    $scope.fullResults = [];

    /**
     * @memberof MainController
     * @property filters
     * @type object
     * @description
     * Filters from JSON file
     * Organized as nested objects where the first level is the table they relate to.
     * The second is the filter itself.
     *
     * See filters.json for the exact structure
     */
    $scope.filters = {};

    /**
     * @memberof MainController
     * @property currentPage
     * @description The current page of results (starts at page 1)
     * @type number
     */
    $scope.currentPage = 1;

    /**
     * @memberof MainController
     * @property pageSize
     * @description The number of results displayed on a page of search results
     * @type number
     */
    $scope.pageSize = 15;

    /**
     * @memberof MainController
     * @property persons
     * @description Storts an array of objects of user stats information
     * @type array
     */
    $scope.persons = [];

    /** Makes call to factory method to get person data
     * @memberof MainController
     * @function getPersons
     */
    $scope.getPersons = function() {
        serverComm.getPersons().success(function(data) {
            $scope.persons = data.rows;
            for (var i = 0; i < $scope.persons.length; i++) {
                $scope.persons[i].weighted_average = (2 * $scope.persons[i].species_rate + $scope.persons[i].gender_rate + $scope.persons[i].age_rate + $scope.persons[i].number_rate) / 5;
            }
            $scope.personTableOrder = [
                "person_id",
                "number_of_classifications",
                "weighted_average",
                "species_rate",
                "gender_rate",
                "age_rate",
                "number_rate"
            ];
        });
    };

    /** Calculate how many results are on current page
     * @memberof MainController
     * @function rowsShown
     * @returns {number} Number of results on the current page
     */
    $scope.rowsShown = function() {
        if ((($scope.currentPage - 1) * $scope.pageSize) + $scope.pageSize < $scope.numResults) {
            return Number((($scope.currentPage - 1) * $scope.pageSize) + $scope.pageSize);
        } else {
            return $scope.numResults;
        }
    };

    /** Make request to results method in factory to get a page of results
     * @memberof MainController
     * @function getResults
     * @param {number} [page]  Index of current page
     */
    $scope.getResults = function(page) {
        $("#loader").fadeTo("fast", 0.7);
        if (page) {
            $scope.currentPage = page;
        }
        serverComm.getPhotos($scope.filters, $scope.currentPage, $scope.pageSize, $scope.isSequence).success(function(data) {
            $scope.results = data.rows;
            $scope.numResults = data.count;
            for (var i = 0; i < $scope.results.length; i++) {
                var result = $scope.results[i];
                var parts = result.Photo.dirname.split("/");
                $scope.results[i].Photo.URL = mammalwebBaseURL + parts[parts.length - 2] + "/" + parts[parts.length - 1] + "/" + result.Photo.filename;
            }
            $("#loader").fadeOut("slow");
        });
    };

    /** Make request to results method in factory to get all results
     * @memberof MainController
     * @function getFullResults
     */
    $scope.getFullResults = function() {
        $("#loader").fadeTo("fast", 0.7);
        serverComm.getFullPhotos($scope.filters, $scope.isSequence).success(function(data) {
            $scope.fullResults = data.rows;
            $("#loader").fadeOut("slow");
        });
    };

    /** Gets and downloads CSV data for current query
     * @memberof MainController
     * @function downloadCSV
     */
    $scope.downloadCSV = function() {
        $("#loader").fadeTo("fast", 0.7);
        serverComm.getPhotosCSV($scope.filters, $scope.isSequence).success(function(data) {
            console.log("Data:", data);
            $("#loader").fadeOut("slow");
            dataLines = data.split("\n");
            for (var i in dataLines) {
                lineSplit = dataLines[i].split(",");
                for (var j in lineSplit) {
                    //lineSplit[j].replace(/"/g, '"');
                }
                dataLines[i] = lineSplit.join(',');

            }
            data = dataLines.join('\r\n');
            $scope.url = "data:application/csv;charset=utf-8," + encodeURIComponent(data);

            //Hack for downloading as a file
            // 		1. Create a hyperlink with download attribute and csv data as  href
            // 		2. use JQuery to click the hyperlink
            // 		3. remove hyperlink
            $('body').append('<a class="b" href="' + $scope.url + '" target="_blank" download="mammal.csv"></a>');
            $('.b').click(function() {
                window.location = $(this).attr('href');
            }).click();
            $(".b").remove();
        });
    };

    /** Gets options from database
     * @memberof MainController
     * @function getOptions
     */
    $scope.getOptions = function() {
        $("#loader").fadeTo("fast", 0.7);
        serverComm.getOptions().success(function(data) {
            //Put options into correct format (see the definition of $scope.options for details)
            $scope.options = {};
            for (var i = 0; i < data.length; i++) {
                if (!$scope.options.hasOwnProperty(data[i].struc)) {
                    $scope.options[data[i].struc] = {};
                }
                $scope.options[data[i].struc][data[i].option_id] = data[i].option_name;
            }
        });
    };

    /** Converts an option into a human readable string using a lookup
     * @memberof MainController
     * @function getOptionName
     * @param {number} optionNum  An option number to be converted
     * @returns {string} Human readable version from lookup
     */
    $scope.getOptionName = function(optionNum) {
        for (var key in $scope.options) { //Try each struc type in options
            if ($scope.options[key].hasOwnProperty(optionNum)) { //if the optionNum is in the struc
                return $scope.readable($scope.options[key][optionNum]); //return option name
            }
        }
    };

    /** Get filter JSON from file
     * @memberof MainController
     * @function getFilters
     */
    $scope.getFilters = function() {
        serverComm.getFilters().success(function(data) {
            $scope.filters = data;
        });
    };

    /** Converts a string into a human-readable format
     * @memberof MainController
     * @function readable
     * @param {string} s  An option number to be converted
     * @returns {string} Human readable version converting spaces to underscores, removing _id, correctly capitalized
     */
    $scope.readable = function(s) {
        if (typeof s === "undefined") {
            return "";
        }
        s = s.replace(/_id/, "");
        s = s.replace(/_/g, " ");
        s = s.replace(/([A-Z])/g, ' $1');
        s = s.replace(/<\/?[^>]+(>|$)/g, "");
        s = s.replace(/^./, function(str) {
            return str.toUpperCase();
        });
        return s;
    };


    //Watchers to call functions when objects change
    $scope.$watch('filters', function(newVal, oldVal) {
        $scope.currentPage = 1;
        $scope.getResults();
    }, true);

    $scope.$watch('isSequence', function(newVal, oldVal) {
        $scope.currentPage = 1;
        $scope.getResults();
    }, true);

    //Initial calls to get data on page load
    $scope.getResults();
    $scope.getOptions();
    $scope.getFilters();
    $scope.getPersons();

}]);




/**
 * @memberof adminApp
 * @ngdoc controller
 * @name GraphsController
 * @param $scope {service} controller scope
 */
adminApp.controller('GraphsController', ['$scope', function($scope) {

    /**
     * @memberof GraphsController
     * @property chartTypes
     * @type array
     * @description Types of Google chart for dropdown
     */
    $scope.chartTypes = ["Table", "PieChart", "BarChart", "ColumnChart", "LineChart", "ScatterChart", "AreaChart"];

    /** Converts a field value into its displayable version based on it's type
     * @memberof GraphsController
     * @function getValue
     * @param {number} val value of the field
     * @param {object} field object from filters
     * @returns {string|Date|number} val actual value of field
     */
    getValue = function(val, field) {
				switch(field.type) {
					case "checkboxes":
						return $scope.getOptionName(val);
					case "dateTime":
						return new Date(val);
					default:
						return val;
				}
    };

    /** Formats the results into correct GoogleCharts format based on choice of x,y axis
     * @memberof GraphsController
     * @function makeData
     */
    $scope.makeData = function() {
        console.log($scope.fullResults);

        //if havn't pulled full results then copy in existing ones
        if ($scope.fullResults.length === 0) {
            $scope.fullResults = $scope.results;
        }

        //Stop if an axis hasn't been chosen
        if (typeof $scope.xName == "undefined" || typeof $scope.yName == "undefined") {
            return;
        }

				//Map for filter type to GoogleCharts data type
				// TODO: refactor into filter.json
        var typeMap = {
            "checkboxes": "string",
            "slider": "number",
            "boolean": "number",
            "dateTime": "datetime",
            "coord": "number"
        };

        if ($scope.yName == "countOfXaxis") {
            //Case for when the user picks the COUNTOF(xvariable) choice for the y axis

            // Setup global chart parameters
            xNameSplit = $scope.xName.split(".");
            $scope.chartObject.options.hAxis.title = $scope.readable(xNameSplit[1]);
            $scope.chartObject.options.vAxis.title = "Count of " + $scope.readable(xNameSplit[1]);
            var xField = $scope.filters[xNameSplit[0]][xNameSplit[1]];
            $scope.chartObject.data.cols = [{
                id: "x",
                label: $scope.readable(xNameSplit[1]),
                type: typeMap[xField.type]
            }, {
                id: "y",
                label: "Count of " + $scope.readable(xNameSplit[1]),
                type: "number"
            }];
            $scope.chartObject.data.rows = [];


            //build dictionary to count occurances of each x value
            dataDict = {};
            for (var i in $scope.fullResults) {
                if (Array.isArray($scope.fullResults[i][xNameSplit[0]])) {
                    for (var j = 0; j < $scope.fullResults[i][xNameSplit[0]].length; j++) {
                        xValue = getValue($scope.fullResults[i][xNameSplit[0]][j][xNameSplit[1]], xField);
                        if (dataDict.hasOwnProperty(xValue)) {
                            dataDict[xValue] += 1;
                        } else {
                            dataDict[xValue] = 1;
                        }
                    }
                }
								else{
                    xValue = getValue($scope.fullResults[i][xNameSplit[0]][xNameSplit[1]], xField);
                    if (dataDict.hasOwnProperty(xValue)) {
                        dataDict[xValue] += 1;
                    } else {
                        dataDict[xValue] = 1;
                    }
                }

            }

            //Convert to rows
            for (var key in dataDict) {
                $scope.chartObject.data.rows.push({
                    c: [{v: key}, {v: dataDict[key]}]
                });
            }
        } else { //Case of 2 normal variables

            //split axis names into [tableName,fieldName]
            xNameSplit = $scope.xName.split(".");
            yNameSplit = $scope.yName.split(".");

            //set axis titles
            $scope.chartObject.options.vAxis.title = $scope.readable(yNameSplit[1]);
            $scope.chartObject.options.hAxis.title = $scope.readable(xNameSplit[1]);

            //check types of variables
            var xField = $scope.filters[xNameSplit[0]][xNameSplit[1]];
            var yField = $scope.filters[yNameSplit[0]][yNameSplit[1]];

            //Set headings and types
            $scope.chartObject.data.cols = [{
                id: "x",
                label: $scope.readable(xNameSplit[1]),
                type: typeMap[xField.type]
            }, {
                id: "y",
                label: $scope.readable(yNameSplit[1]),
                type: typeMap[yField.type]
            }];

            //Empty data rows
            $scope.chartObject.data.rows = [];

            for (var i = 0; i < $scope.fullResults.length; i++) {
                //Deal with data containing arrays
                loopX = 1;
                loopY = 1;

                xChanged = false;
                yChanged = false;

                //Set loop conditions to deal with the case of many-to-many relationships in the db
                // e.g. if we allow multiple classifications we need to repeat the rows over each classification
                if (Array.isArray($scope.fullResults[i][xNameSplit[0]])) {
                    loopX = $scope.fullResults[i][xNameSplit[0]].length;
                } else {
                    $scope.fullResults[i][xNameSplit[0]] = [$scope.fullResults[i][xNameSplit[0]]];
                    xChanged = true;
                }
                if (Array.isArray($scope.fullResults[i][yNameSplit[0]])) {
                    loopY = $scope.fullResults[i][yNameSplit[0]].length;
                } else {
                    $scope.fullResults[i][yNameSplit[0]] = [$scope.fullResults[i][yNameSplit[0]]];
                    yChanged = true;
                }

                //Add rows
                for (var j = 0; j < loopX; j++) {
                    for (var k = 0; k < loopY; k++) {
                        xValue = getValue($scope.fullResults[i][xNameSplit[0]][j][xNameSplit[1]], xField);
                        yValue = getValue($scope.fullResults[i][yNameSplit[0]][k][yNameSplit[1]], yField);
                        $scope.chartObject.data.rows.push({
                            c: [{
                                v: xValue
                            }, {
                                v: yValue
                            }]
                        });
                    }
                }

                //Restore data structure of $scope.fullResults
                if (xChanged) {
                    $scope.fullResults[i][xNameSplit[0]] = $scope.fullResults[i][xNameSplit[0]][0];
                }
                if (yChanged) {
                    $scope.fullResults[i][yNameSplit[0]] = $scope.fullResults[i][yNameSplit[0]][0];
                }
            }
        }
    };

    /**
     * @memberof GraphsController
     * @property chartStyle
     * @type string
     * @description CSS styles to be passed to GoogleCharts module
     */
    $scope.chartStyle = "height:300px;width:100%";

    /**
     * @memberof GraphsController
     * @property chartObject
     * @type object
     * @description options and data passed to GoogleCharts module
     */
    $scope.chartObject = {
        "type": "Table",
        "displayed": true,
        "data": {
            "cols": [

            ],
            "rows": [

            ]
        },
        "options": {
            "title": "Chart title",
            "isStacked": "true",
            "fill": 20,
            "displayExactValues": true,
            "vAxis": {
                "title": "Xaxis",
                "gridlines": {
                    "count": 10
                }
            },
            "hAxis": {
                "title": "Yaxis"
            }
        },
        "formatters": {}
    };
}]);
