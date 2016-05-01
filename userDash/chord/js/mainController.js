angular.module('app', []);

var urls = ["http://localhost:8080/", "https://mammalweb.herokuapp.com/"];
// Ajax Service
angular.module('app').factory('ajax', ['$http', function($http) {
    return {
        getPhotos: function(query, pageNum, pageSize) {
            return $http.post(urls[1] + 'photo?sequence=true&pageNum=' + pageNum + '&pageSize=' + pageSize, query).success(function() {});
        },
        getPhotosCSV: function(query, isSequence) {
            return $http.post(urls[1] + 'photo?output=csv&sequence=' + isSequence, query).success(function() {});
        },
        getOptions: function() {
            return $http.get(urls[1] + 'options').success(function() {});
        },
        getFilters: function() {
            return $http.get('../commonDependancies/filters.json').success(function() {});
        }
    };

}]);


angular.module('app').controller('mainCntrl', ['$scope','ajax',
function ($scope,serverComm) {

  $scope.master = {}; // MASTER DATA

  $scope.filters = {};
  $scope.hasFilters = false;

  $scope.tooltip = {};

  // FORMATS USED IN TOOLTIP TEMPLATE IN HTML
  $scope.pFormat = d3.format(".1%");  // PERCENT FORMAT
  $scope.qFormat = d3.format(",.0f"); // COMMAS FOR LARGE NUMBERS

  $scope.updateTooltip = function (data) {
    $scope.tooltip = data;
    $scope.$apply();
  }

  $scope.addFilter = function (name) {
    $scope.hasFilters = true;
    $scope.filters[name] = {
      name: name,
      hide: true
    };
    $scope.$apply();
  };

  $scope.update = function () {
    var data = $scope.master;

    if (data && $scope.hasFilters) {
      $scope.drawChords(data.filter(function (d) {
        var fl = $scope.filters;
        var v1 = d.importer1, v2 = d.importer2;

        if ((fl[v1] && fl[v1].hide) || (fl[v2] && fl[v2].hide)) {
          return false;
        }
        return true;
      }));
    } else if (data) {
      $scope.drawChords(data);
    }
  };

  // IMPORT THE CSV DATA
  d3.csv('../data/trade.csv', function (err, data) {
    $scope.master = [];

    serverComm.getOptions().success(function(data) {
        console.log("data", data);
        $scope.options = {};
        for (var i = 0; i < data.length; i++) {
            if (!$scope.options.hasOwnProperty(data[i].struc)) {
                $scope.options[data[i].struc] = {};
            }
            $scope.options[data[i].struc][data[i].option_id] = data[i].option_name;
        }
    serverComm.getPhotos($scope.filters, 1, 500).success(function(data) {
        console.log("Data:", data);
        var results = data.rows;

        //Build dictionary of site:[species]
        siteDict = {};
        for (var i in results){
          var r = results[i];
          var site_id = r.Site.site_id;
          if (!siteDict.hasOwnProperty(site_id)){
            siteDict[site_id] = [];
          }
          siteDict[site_id].push($scope.getOptionName(r.Classification[0].species));
        }
        //For each species find common species
        for (var site in siteDict){
          for (var i in siteDict[site]){
            for (var j=i;i<siteDict[site].length;i++){
              $scope.master.push({
                flow1:1,
                flow2:1,
                importer1: siteDict[site][i],
                importer2: siteDict[site][j]
                  });
            }
          }
        }
        console.log(siteDict);

        console.log($scope.master);
        $scope.update();
    });

        });

  });


  $scope.getOptionName = function(optionNum) {
      //Function to convert an option into human readable string
      for (var key in $scope.options) {
          if ($scope.options[key].hasOwnProperty(optionNum)) {
              return $scope.readable($scope.options[key][optionNum]);
          }
      }
      return "";
  };
  $scope.readable = function(string) {
      if (typeof string === "undefined") {
          return "";
      }
      string = string.replace(/_id/, "");
      string = string.replace(/_/g, " ");
      string = string.replace(/([A-Z])/g, ' $1');
      string = string.replace(/<\/?[^>]+(>|$)/g, "");
      string = string.replace(/^./, function(str) {
          return str.toUpperCase();
      });
      return string;
  };


}]);
