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

  var getHTML = function(item){
    var html = '<div>'
    var url = "http://www.mammalweb.org/biodivimages/"+item.URL;
    html += '<a href="'+url+'" target="_blank"><img width=200 src="'+url+'"></a></div>';
    html += '<b>'+item.upload_filename+'</b><br>';
    html += item.Site.site_name+'<br>';
    html += item.taken+'';
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
            message: getHTML($scope.results[i]),
            icon:{
              iconUrl: '../animalIcons/'+$scope.results[i].Classification[0].species+'.png',
              shadowUrl: '../animalIcons/shadow.png',
              iconSize:     [30, 30],
              shadowSize:   [30, 30],
              iconAnchor:   [15, 5],
              shadowAnchor: [10, 0]
            }
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
        layers:{
        baselayers: {
                satellite: {
                  name: "Satellite",
                  type: "xyz",
                  url: "http://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}"
                },
                osm: {
                    name: 'OpenStreetMap',
                    url: 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                    type: 'xyz'
                },
                googleTerrain: {
                    name: 'Google Terrain',
                    layerType: 'TERRAIN',
                    type: 'google'
                },
                googleHybrid: {
                    name: 'Google Hybrid',
                    layerType: 'HYBRID',
                    type: 'google'
                },
                googleRoadmap: {
                    name: 'Google Streets',
                    layerType: 'ROADMAP',
                    type: 'google'
                }
            }
        },
        defaults: {
            scrollWheelZoom: true
        }
    });

  $scope.getResults();



                


}]);