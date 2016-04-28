var userApp = angular.module('userDash', ['rzModule', 'ui.bootstrap',"checklist-model",'datetimepicker','leaflet-directive','pageslide-directive','ui.router','ngAnimate','ngVis','rt.debounce']);

var urls = ["http://localhost:8080/","https://mammalweb.herokuapp.com/"];

var mammalwebBaseURL = "http://www.mammalweb.org/biodivimages/"

userApp.config(function($stateProvider, $urlRouterProvider) {
  //
  // For any unmatched url, redirect to /state1
  $urlRouterProvider.otherwise("/map");
  //
  // Now set up the states
  $stateProvider
    .state('map', {
      url: "/map",
      templateUrl: "partials/map.html",
      controller: mapController
    })
    .state('slideshow', {
      url: "/slideshow",
      templateUrl: "partials/slideshow.html",
      controller: slideshowController
    });
});

//map route controller
var mapController = function($scope,$filter) {

        var getHTML = function(item){
		   	var html = '<div>'
		   	var url = item.Photo.URL;
		    html += '<a href="'+url+'" target="_blank"><img width=200 src="'+url+'"></a></div>';
		    html += '<b>'+$scope.getOptionName(item.Classification[0].species)+'</b><br>';
		    html += item.Site.site_name+'<br>';
		    html += $filter('date')(item.taken,'dd/MM/yyyy HH:mm')+'';
		    return html;
		}

        var seed = 1;
        function random() {
            var x = Math.sin(seed++) * 10000;
            return x - Math.floor(x);
        }

        $scope.$watch("results",function(newVal,oldVal){


        	$scope.markers = {};
        	for (var i = 0; i < $scope.results.length; i++)
        	{
				if ($scope.results[i].Site != null){
                    seed = $scope.results[i].Photo.photo_id
		        	$scope.markers["m"+i] = {
		            lat: $scope.results[i].Site.lat+(0.5-random())*0.001,
		            lng: $scope.results[i].Site.lon+(0.5-random())*0.001,
		            message: getHTML($scope.results[i]),
		            icon:{
		              iconUrl: '../../animalIcons/'+$scope.results[i].Classification[0].species+'.png',
		              shadowUrl: '../../animalIcons/shadow.png',
		              iconSize:     [30, 30],
		              shadowSize:   [30, 30],
		              iconAnchor:   [15, 5],
		              shadowAnchor: [10, 0]
		            }
		          }
		        }
		    }
        });


  angular.extend($scope, {
        center: {
            lat: 54.7,
            lng: -1.4,
            zoom: 9
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
};



var slideshowController = function ($scope, $timeout, QueueService) {
    var INTERVAL = 10000;

    function setCurrentSlideIndex(index) {
        $scope.currentIndex = index;
    }

    function isCurrentSlideIndex(index) {
        return $scope.currentIndex === index;
    }

    function nextSlide() {
        $scope.currentIndex = ($scope.currentIndex < $scope.results.length - 1) ? ++$scope.currentIndex : 0;
        $timeout(nextSlide, INTERVAL);
    }

    function setCurrentAnimation(animation) {
        $scope.currentAnimation = animation;
    }

    function isCurrentAnimation(animation) {
        return $scope.currentAnimation === animation;
    }

    function loadSlides() {
        QueueService.loadManifest($scope.results);
    }

    $scope.$on('queueProgress', function(event, queueProgress) {
        $scope.$apply(function(){
            $scope.progress = queueProgress.progress * 100;
        });
    });

    $scope.loaded = false;

    $timeout(nextSlide, INTERVAL);

    $scope.progress = 0;
    $scope.loaded = true;
    $scope.currentIndex = 0;
    $scope.currentAnimation = 'fade-in-animation';

    $scope.setCurrentSlideIndex = setCurrentSlideIndex;
    $scope.isCurrentSlideIndex = isCurrentSlideIndex;
    $scope.setCurrentAnimation = setCurrentAnimation;
    $scope.isCurrentAnimation = isCurrentAnimation;

    loadSlides();
};

userApp.factory('QueueService', function($rootScope){
    var queue = new createjs.LoadQueue(true);

    function loadManifest(manifest) {
        queue.loadManifest(manifest);

        queue.on('progress', function(event) {
            $rootScope.$broadcast('queueProgress', event);
        });

        queue.on('complete', function() {
            $rootScope.$broadcast('queueComplete', manifest);
        });
    }

    return {
        loadManifest: loadManifest
    }
})

userApp.animation('.fade-in-animation', function ($window) {
    return {
        enter: function (element, done) {
            TweenMax.fromTo(element, 1, { opacity: 0}, {opacity: 1, onComplete: done,delay:1});
        },

        leave: function (element, done) {
            TweenMax.to(element, 1, {opacity: 0, onComplete: done});
        }
    };
});



// Ajax Service
userApp.factory('ajax', ['$http', function($http) {
    return {
    getPhotos: function(query,pageNum,pageSize) {
      return $http.post(urls[1]+'photo?sequence=true&pageNum='+pageNum+'&pageSize='+pageSize,query).success(function() {
      });
    },
    getPhotosCSV: function(query,isSequence){
        return $http.post(urls[1]+'photo?output=csv&sequence='+isSequence,query).success(function() {
      });
    },
    getOptions: function() {
      return $http.get(urls[1]+'options').success(function() {
      });
    },
    getFilters: function() {
      return $http.get('../../adminDash/filters.json').success(function() {
      });
    }
  };

}]);

//data controller
userApp.controller('dataController',['$scope','$location','$timeout','ajax', function($scope, $location,$timeout,serverComm) {
    $scope.filtersOpen = false;
    $scope.timelineOpen = true;
    $scope.navbarOpen = true;
    $scope.results = "data";

    $scope.results = []; //contains the results from the server
    $scope.options = {};
    
    
    //PAGE functions
    $scope.currentPage = 1;
    $scope.pageSize = 15;

    var moved;

    $scope.mouseMove = function(){
        moved = true;
        $scope.navbarOpen = true;
        $timeout($scope.hideNav, 15000);
        
    };

    $scope.hideNav = function(){
        if (!moved){
            $scope.navbarOpen = false;
        }
        else{
            moved = false;
            $timeout($scope.hideNav, 15000);
        }
    };

    $scope.getResults = function(){
        $("#loader").fadeTo("fast", 0.7);
        serverComm.getPhotos($scope.filters,1,100).success(function(data) {
            console.log("Data:",data);
            $scope.results = data.rows;
            $scope.numResults = data.count;
            for (var i = 0; i < $scope.results.length; i++) {
                var result = $scope.results[i];
                var parts = result.Photo.dirname.split("/");

                $scope.results[i].Photo.URL = mammalwebBaseURL + parts[parts.length - 2]+"/"+parts[parts.length - 1]+"/"+result.Photo.filename;
            }
            $("#loader").fadeOut("slow");
        });
  };

  $scope.getOptions = function(){
        //console.log("get options");
        $("#loader").fadeTo("fast", 0.7);
        serverComm.getOptions().success(function(data) {
                console.log("data",data);
                $scope.options = {};
                for (var i = 0; i < data.length; i++) {
                    //console.log(data[i]["struc"])
                    if (!$scope.options.hasOwnProperty(data[i]["struc"]) ){
                        $scope.options[data[i]["struc"]] = {}
                    }
                    //Check for and remove "Unknown"
                    //if (!(data[i]["option_name"] === "Unknown")){
                        $scope.options[data[i]["struc"]][data[i]["option_id"]] = data[i]["option_name"];
                    //}*/ //Removed as breaks things
                }
                //console.log($scope.options)
                
        });
    };

    $scope.getOptionName = function(optionNum){
        //Function to convert an option into human readable string
        for (key in $scope.options){
            if($scope.options[key].hasOwnProperty(optionNum)){
                return $scope.options[key][optionNum]
            }
        }
        return "";
    }

    $scope.getFilters = function(){
        serverComm.getFilters().success(function(data) {
            console.log("FITLERS",data)
                //console.log(data);
                $scope.filters = data
                
        });
    }
    
     $scope.readable = function(string) {
        if (typeof string === "undefined")
        {
            return "";
        }
        string = string.replace(/_id/,"");
        string = string.replace(/_/g, " ");
        string = string.replace(/([A-Z])/g, ' $1');
        string = string.replace(/^./, function(str){ return str.toUpperCase(); });
        return string
    }

    $scope.isActive = function (viewLocation) { 
        return viewLocation === $location.path();
    };


    $scope.$watch('filters', function(newVal, oldVal){
        $scope.getResults();
    }, true);

  $scope.filters = {}

  $scope.getResults();
  $scope.getOptions();
  $scope.getFilters();
}]);
