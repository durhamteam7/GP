var userApp = angular.module('userDash', ['rzModule', 'ui.bootstrap',"checklist-model",'datetimepicker','leaflet-directive']);

var urls = ["http://localhost:8080/","https://mammalweb.herokuapp.com/"];



userApp.factory('ajax', ['$http', function($http) {
	return {
    getPhotos: function(query,pageNum,pageSize) {
    	//$http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
        // Delete the Requested With Header
        //delete $http.defaults.headers.common['X-Requested-With'];
      return $http.post(urls[1]+'photo?pageNum='+pageNum+'&pageSize='+pageSize,query).success(function() {
      });
    },
     getOptions: function() {
      return $http.get(urls[1]+'options').success(function() {
      });
    }
  };

}]);

userApp.controller('MainController', ['$scope','ajax', function($scope,serverComm)
{
	//$("#loader").fadeTo("fast", 0.7);
  // make map

  var imgURL = "http://www.mammalweb.org/biodivimages/person_182/site_2/55fc9b6303e88ff8d0435eecc3e24683.jpg";
  var html = '<img width=200 src="'+imgURL+'">';

  var getHTML = function(imgURL){
    var html = '<img width=200 src="http://www.mammalweb.org/biodivimages/'+imgURL+'">';
    return html;
  }

  $scope.getResults = function(){
    $("#loader").fadeTo("fast", 0.7);
    serverComm.getPhotos({},1,100).success(function(data) {
        console.log("Data:",data);
        $scope.results = data.rows;
        $scope.numResults = data.count;
        for (var i = 0; i < $scope.results.length; i++) {
          //Form correct URL
          var result = $scope.results[i];
          var parts = result.dirname.split("/");
          $scope.results[i].URL = parts[parts.length - 2]+"/"+parts[parts.length - 1]+"/"+result.filename;

          $scope.markers["m"+i] = {
            lat: $scope.results[i].Site.lat,
            lng: $scope.results[i].Site.lon,
            message: getHTML($scope.results[i].URL)
          }

        }



        $("#loader").fadeOut("slow");
    });
  };


  angular.extend($scope, {
        center: {
            lat: 55,
            lng: 0,
            zoom: 7
        },
        markers:{},
        tiles: {
            url: "http://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}"
        },
        defaults: {
            scrollWheelZoom: true
        }
    });

  $scope.getResults();



                


}]);