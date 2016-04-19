var userApp = angular.module('userDash', ['rzModule', 'ui.bootstrap',"checklist-model",'datetimepicker','leaflet-directive','pageslide-directive','ui.router','ngAnimate']);

var urls = ["http://localhost:8080/","https://mammalweb.herokuapp.com/"];

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
var mapController = function($scope) {

        var getHTML = function(item){
		    console.log(item)
		   	var html = '<div>'
		   	var url = item.URL;
		    html += '<a href="'+url+'" target="_blank"><img width=200 src="'+url+'"></a></div>';
		    html += '<b>'+item.upload_filename+'</b><br>';
		    html += item.Site.site_name+'<br>';
		    html += item.taken+'';
		    return html;
		}

        $scope.$watch("results",function(newVal,oldVal){


        	$scope.markers = {};
        	for (var i = 0; i < $scope.results.length; i++)
        	{
				if ($scope.results[i].Site != null){
		        	$scope.markers["m"+i] = {
		            lat: $scope.results[i].Site.lat,
		            lng: $scope.results[i].Site.lon,
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
};



var slideshowController = function ($scope, $timeout, QueueService) {
    var INTERVAL = 5000;

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
    $scope.currentAnimation = 'slide-left-animation';

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

app.factory('QueueService', function($rootScope){
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


userApp.animation('.slide-left-animation', function ($window) {
    return {
        enter: function (element, done) {
            TweenMax.fromTo(element, 1, { left: $window.innerWidth}, {left: 0, onComplete: done});
        },

        leave: function (element, done) {
            TweenMax.to(element, 1, {left: -$window.innerWidth, onComplete: done});
        }
    };
});

userApp.animation('.slide-down-animation', function ($window) {
    return {
        enter: function (element, done) {
            TweenMax.fromTo(element, 1, { top: -$window.innerHeight}, {top: 0, onComplete: done});
        },

        leave: function (element, done) {
            TweenMax.to(element, 1, {top: $window.innerHeight, onComplete: done});
        }
    };
});

userApp.animation('.fade-in-animation', function ($window) {
    return {
        enter: function (element, done) {
            TweenMax.fromTo(element, 1, { opacity: 0}, {opacity: 1, onComplete: done});
        },

        leave: function (element, done) {
            TweenMax.to(element, 1, {opacity: 0, onComplete: done});
        }
    };
});




//getting the data
userApp.factory('ajax', ['$http', function($http) {
	return {
    getPhotos: function(query,pageNum,pageSize) {
      return $http.post(urls[1]+'photo?pageNum='+pageNum+'&pageSize='+pageSize,query).success(function() {
      });
    },
     getOptions: function() {
      return $http.get(urls[1]+'options').success(function() {
      });
    }
  };

}]);

//data controller
userApp.controller('dataController',['$scope', 'ajax', function($scope,serverComm) {
    $scope.checked = false;
        $scope.results = "data";
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
          $scope.results[i].URL = "http://www.mammalweb.org/biodivimages/"+parts[parts.length - 2]+"/"+parts[parts.length - 1]+"/"+result.filename;
        }



        $("#loader").fadeOut("slow");
    });
  };

  $scope.getResults();
}]);



userApp.directive('bsActiveLink', ['$location', function ($location) {
return {
    restrict: 'A', //use as attribute 
    replace: false,
    link: function (scope, elem) {
        //after the route has changed
        scope.$on("$routeChangeSuccess", function () {
            var hrefs = ['/#' + $location.path(),
                         '#' + $location.path(), //html5: false
                         $location.path()]; //html5: true
            angular.forEach(elem.find('a'), function (a) {
                a = angular.element(a);
                if (-1 !== hrefs.indexOf(a.attr('href'))) {
                    a.parent().addClass('active');
                } else {
                    a.parent().removeClass('active');   
                };
            });     
        });
    }
}
}])