var userApp = angular.module('userDash', ['utilities','serverComms', 'rzModule', 'ui.bootstrap', "checklist-model", 'datetimepicker', 'leaflet-directive', 'pageslide-directive', 'ui.router', 'ngAnimate', 'ngVis', 'rt.debounce','toggle-switch']);

var mammalwebBaseURL = "http://www.mammalweb.org/biodivimages/";

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
        })
        .state('chord', {
            url: "/chord",
            templateUrl: "partials/chord.html",
            controller: chordController
        });
});

/** Controller for the mapping section of the userDash.
 * @memberof userApp
 * @ngdoc controller
 * @name mapController
 * @param $scope {service} controller scope
 * @param $filter
 */
var mapController = function($scope, $filter) {

  /** Generates pop-up html for a photo
   * @param {Object} item photo JSON
   * @returns {string} HTML string of popup
   */
    var getHTML = function(item) {
        var html = '<div>';
        var url = item.Photo.URL;
        html += '<a href="' + url + '" target="_blank"><img width=200 src="' + url + '"></a></div>';
        html += '<b>' + $scope.getOptionName(item.Classification[0].species) + '</b><br>';
        html += item.Site.site_name + '<br>';
        html += $filter('date')(item.Photo.taken, 'dd/MM/yyyy HH:mm') + '';
        return html;
    };

    var seed = 1;
    /** Generates a random decimal
     * @memberof mapController
     * @name random
     * @returns {int} Produces a random number from a seed
     */
    function random() {
        var x = Math.sin(seed++) * 10000;
        return x - Math.floor(x);
    }

    $scope.$watch("results", function(newVal, oldVal) {
        $scope.markers = {};
        for (var i = 0; i < $scope.results.length; i++) {
            if ($scope.results[i].Site !== null) {
                seed = $scope.results[i].Photo.photo_id;
                $scope.markers["m" + i] = {
                    lat: $scope.results[i].Site.lat + (0.5 - random()) * 0.001,
                    lng: $scope.results[i].Site.lon + (0.5 - random()) * 0.001,
                    message: getHTML($scope.results[i]),
                    icon: {
                        iconUrl: '../../animalIcons/' + $scope.results[i].Classification[0].species + '.png',
                        shadowUrl: '../../animalIcons/shadow.png',
                        iconSize: [30, 30],
                        shadowSize: [30, 30],
                        iconAnchor: [15, 5],
                        shadowAnchor: [10, 0]
                    }
                };
            }
        }
    });


    angular.extend($scope, {
        center: {
            lat: 54.7,
            lng: -1.4,
            zoom: 9
        },
        markers: {},
        layers: {
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


/** Controller for the slideshow section of the userDash.
 * @memberof userApp
 * @ngdoc controller
 * @name slideshowController
 * @param $scope {service} controller scope
 * @param $filter
 */
var slideshowController = function($scope, $timeout, QueueService) {
    var INTERVAL = 10000;

    var timeoutPromise;

    /** Setter for currentIndex
     * @memberof slideshowController
     * @param {number} index New slide index
     */
    function setCurrentSlideIndex(index) {
        $scope.currentIndex = index;
    }

    /** Conditional test for currentIndex
     * @memberof slideshowController
     * @function isCurrentSlideIndex
     * @param {number} index Value to check against
     * @return {boolean} True if current slide is index
     */
    function isCurrentSlideIndex(index) {
        return $scope.currentIndex === index;
    }

    /** Increments slide
     * @memberof slideshowController
     * @param {number} index Value to check against
     * @return {boolean} True if current slide is index
     */
    $scope.nextSlide = function() {
        $scope.currentIndex = ($scope.currentIndex < $scope.results.length - 1) ? ++$scope.currentIndex : 0;
        timeoutPromise = $timeout($scope.nextSlide, INTERVAL);
    };
    $scope.previousSlide = function() {
        $scope.currentIndex = ($scope.currentIndex < $scope.results.length - 1) ? --$scope.currentIndex : 0;
        timeoutPromise = $timeout($scope.nextSlide, INTERVAL);
    };

    $scope.nextSlideOnPress = function() {
        $timeout.cancel(timeoutPromise);
        $scope.nextSlide();
    };
    $scope.previousSlideOnPress = function() {
        $timeout.cancel(timeoutPromise);
        $scope.previousSlide();
    };


    /*function setCurrentAnimation(animation) {
        $scope.currentAnimation = animation;
    }

    function isCurrentAnimation(animation) {
        return $scope.currentAnimation === animation;
    }*/

    /** Make call to QueueService
     * @memberof slideshowController
     */
    function loadSlides() {
        QueueService.loadManifest($scope.results);
    }

    $scope.$on('queueProgress', function(event, queueProgress) {
        $scope.$apply(function() {
            $scope.progress = queueProgress.progress * 100;
        });
    });

    $scope.loaded = false;

    timeoutPromise = $timeout($scope.nextSlide, INTERVAL);

    $scope.progress = 0;
    $scope.loaded = true;
    $scope.currentIndex = 0;
    $scope.currentAnimation = 'fade-in-animation';

    $scope.setCurrentSlideIndex = setCurrentSlideIndex;
    $scope.isCurrentSlideIndex = isCurrentSlideIndex;
    //$scope.setCurrentAnimation = setCurrentAnimation;
    //$scope.isCurrentAnimation = isCurrentAnimation;

    loadSlides();
};

/**
 * @memberof userApp
 * @ngdoc factory
 * @name QueueService
 * @param {scope} $rootScope Scope of controller
 * @description
 *   Queues loading of images
 */
userApp.factory('QueueService', function($rootScope) {
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
    };
});

userApp.animation('.fade-in-animation', function($window) {
    return {
        enter: function(element, done) {
            TweenMax.fromTo(element, 1, {
                opacity: 0
            }, {
                opacity: 1,
                onComplete: done,
                delay: 1
            });
        },

        leave: function(element, done) {
            TweenMax.to(element, 1, {
                opacity: 0,
                onComplete: done
            });
        }
    };
});


/** Controller for the chord diagram section of the userDash.
 * @memberof userApp
 * @ngdoc controller
 * @name chordController
 * @param $scope {service} controller scope
 * @param $filter
 */
var chordController = function ($scope) {

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
    $scope.$watch("results", function(newVal, oldVal) {
      $scope.master = [];

          //Build dictionary of site:[species]
          siteDict = {};
          for (var i in $scope.results){
            var r = $scope.results[i];
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

};



//data controller
userApp.controller('dataController', ['$scope', '$location', '$timeout', 'ajax', function($scope, $location, $timeout, serverComm) {
    $scope.filtersOpen = false;
    $scope.timelineOpen = true;
    $scope.navbarOpen = true;
    $scope.results = "data";

    $scope.person_id = 310;

    $scope.datasetSize = 100;

    $scope.results = []; //contains the results from the server
    $scope.options = {};

    /**
     * @memberof dataController
     * @property filterOpen
     * @description Stores the open/closed status of checkbox filters
     * @type object
     */
    $scope.filterOpen = {};

    $scope.$watch('filtersOpen', function(newVal, oldVal) {
        $timeout(function() {
            $scope.$broadcast('rzSliderForceRender');
        },700);
    });

    $scope.$watch('datasetSize', function(newVal, oldVal) {
        $scope.getResults();
    });

    //PAGE functions
    $scope.currentPage = 1;
    $scope.pageSize = 15;

    var moved;

    $scope.mouseMove = function() {
        moved = true;
        $scope.navbarOpen = true;
        $timeout($scope.hideNav, 15000);

    };

    $scope.hideNav = function() {
        if (!moved) {
            $scope.navbarOpen = false;
        } else {
            moved = false;
            $timeout($scope.hideNav, 15000);
        }
    };

    $scope.getResults = function() {
        $("#loader").fadeTo("fast", 0.7);
        serverComm.getPhotos($scope.filters, 1, $scope.datasetSize, true, $scope.person_id,$scope.isFavourites).success(function(data) {
            console.log("Data:", data);
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

    $scope.getFullResults = function() {
        $("#loader").fadeTo("fast", 0.7);
        serverComm.getFullPhotos($scope.filters, $scope.isSequence).success(function(data) {
            $scope.results = data.rows;
            console.log("Got full data");
            $("#loader").fadeOut("slow");
        });
    };

    $scope.getOptions = function() {
        //console.log("get options");
        $("#loader").fadeTo("fast", 0.7);
        serverComm.getOptions().success(function(data) {
            console.log("data", data);
            $scope.options = {};
            for (var i = 0; i < data.length; i++) {
                //console.log(data[i]["struc"])
                if (!$scope.options.hasOwnProperty(data[i].struc)) {
                    $scope.options[data[i].struc] = {};
                }
                //Check for and remove "Unknown"
                //if (!(data[i]["option_name"] === "Unknown")){
                $scope.options[data[i].struc][data[i].option_id] = data[i].option_name;
                //}*/ //Removed as breaks things
            }
            //console.log($scope.options)

        });
    };

    $scope.getOptionName = function(optionNum) {
        //Function to convert an option into human readable string
        for (var key in $scope.options) {
            if ($scope.options[key].hasOwnProperty(optionNum)) {
                return $scope.readable($scope.options[key][optionNum]);
            }
        }
        return "";
    };

    $scope.getFilters = function() {
        serverComm.getFilters().success(function(data) {
            console.log("FITLERS", data);
            //console.log(data);
            $scope.filters = data;

        });
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

    $scope.isActive = function(viewLocation) {
        return viewLocation === $location.path();
    };

    $scope.addFavourite = function(slide){
      for (var i in slide.Favourites){
        if (slide.Favourites[i].person_id == $scope.person_id){
          console.log("Removing faovurite",slide)
          console.log(slide.Favourites);
          delete slide.Favourites[i];
          console.log(slide.Favourites);
          serverComm.setFavourite($scope.person_id,slide.Photo.photo_id,false);
          return true;
        }
      }
      console.log("Adding faovurite",slide)
      slide.Favourites.push({person_id:$scope.person_id,photo_id:slide.Photo.photo_id});
      serverComm.setFavourite($scope.person_id,slide.Photo.photo_id,true);
    }

    $scope.isFavourite = function(slide){
      for (var i in slide.Favourites){
        if (slide.Favourites[i].person_id == $scope.person_id){
          return true;
        }
      }
      return false;
    }

    $scope.$watch('isFavourites', function(newVal, oldVal) {
      $scope.getResults();
    });

    $scope.$watch('filters', function(newVal, oldVal) {
        $scope.getResults();
    }, true);

    $scope.filters = {};

    function getQueryParams(qs) {
        qs = qs.split('+').join(' ');
        var params = {},
            tokens,
            re = /[?&]?([^=]+)=([^&]*)/g;
        while (tokens = re.exec(qs)) {
            params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]);
        }
        return params;
    }

    function setPersonID(){
        qParam = getQueryParams(document.location.search).person_id;
        if (qParam !== ""){
          $scope.person_id = qParam;
        }
        console.log($scope.person_id);
    }
    setPersonID();
    $scope.getResults();
    $scope.getOptions();
    $scope.getFilters();
}]);


angular.module('userDash').directive('chordDiagram', ['$window', 'matrixFactory',

function ($window, matrixFactory) {

  var link = function ($scope, $el, $attr) {

    var size = [750, 750]; // SVG SIZE WIDTH, HEIGHT
    var marg = [50, 50, 50, 50]; // TOP, RIGHT, BOTTOM, LEFT
    var dims = []; // USABLE DIMENSIONS
    dims[0] = size[0] - marg[1] - marg[3]; // WIDTH
    dims[1] = size[1] - marg[0] - marg[2]; // HEIGHT

    var colors = d3.scale.ordinal()
      .range(['#9C6744','#C9BEB9','#CFA07E','#C4BAA1','#C2B6BF','#121212','#8FB5AA','#85889E','#9C7989','#91919C','#242B27','#212429','#99677B','#36352B','#33332F','#2B2B2E','#2E1F13','#2B242A','#918A59','#6E676C','#6E4752','#6B4A2F','#998476','#8A968D','#968D8A','#968D96','#CC855C', '#967860','#929488','#949278','#A0A3BD','#BD93A1','#65666B','#6B5745','#6B6664','#695C52','#56695E','#69545C','#565A69','#696043','#63635C','#636150','#333131','#332820','#302D30','#302D1F','#2D302F','#CFB6A3','#362F2A']);

    var chord = d3.layout.chord()
      .padding(0.02)
      .sortGroups(d3.descending)
      .sortSubgroups(d3.ascending);

    var matrix = matrixFactory.chordMatrix()
      .layout(chord)
      .filter(function (item, r, c) {
        return (item.importer1 === r.name && item.importer2 === c.name) ||
               (item.importer1 === c.name && item.importer2 === r.name);
      })
      .reduce(function (items, r, c) {
        var value;
        if (!items[0]) {
          value = 0;
        } else {
          value = items.reduce(function (m, n) {
            if (r === c) {
              return m + (n.flow1 + n.flow2);
            } else {
              return m + (n.importer1 === r.name ? n.flow1: n.flow2);
            }
          }, 0);
        }
        return {value: value, data: items};
      });

    var innerRadius = (dims[1] / 2) - 100;

    var arc = d3.svg.arc()
      .innerRadius(innerRadius)
      .outerRadius(innerRadius + 20);

    var path = d3.svg.chord()
      .radius(innerRadius);

    var svg = d3.select($el[0]).append("svg")
      .attr("class", "chart")
      .attr({width: size[0] + "px", height: size[1] + "px"})
      .attr("preserveAspectRatio", "xMinYMin")
      .attr("viewBox", "0 0 " + size[0] + " " + size[1]);

    var container = svg.append("g")
      .attr("class", "container")
      .attr("transform", "translate(" + ((dims[0] / 2) + marg[3]) + "," + ((dims[1] / 2) + marg[0]) + ")");

    var messages = svg.append("text")
      .attr("class", "messages")
      .attr("transform", "translate(10, 10)")
      .text("Updating...");

    $scope.drawChords = function (data) {

      messages.attr("opacity", 1);
      messages.transition().duration(1000).attr("opacity", 0);

      matrix.data(data)
        .resetKeys()
        .addKeys(['importer1', 'importer2'])
        .update()

      var groups = container.selectAll("g.group")
        .data(matrix.groups(), function (d) { return d._id; });

      var gEnter = groups.enter()
        .append("g")
        .attr("class", "group");

      gEnter.append("path")
        .style("pointer-events", "none")
        .style("fill", function (d) { return colors(d._id); })
        .attr("d", arc);

      gEnter.append("text")
        .attr("dy", ".35em")
        .on("click", groupClick)
        .on("mouseover", dimChords)
        .on("mouseout", resetChords)
        .text(function (d) {
          return d._id;
        });

      groups.select("path")
        .transition().duration(2000)
        .attrTween("d", matrix.groupTween(arc));

      groups.select("text")
        .transition()
        .duration(2000)
        .attr("transform", function (d) {
          d.angle = (d.startAngle + d.endAngle) / 2;
          var r = "rotate(" + (d.angle * 180 / Math.PI - 90) + ")";
          var t = " translate(" + (innerRadius + 26) + ")";
          return r + t + (d.angle > Math.PI ? " rotate(180)" : " rotate(0)");
        })
        .attr("text-anchor", function (d) {
          return d.angle > Math.PI ? "end" : "begin";
        });

      groups.exit().select("text").attr("fill", "orange");
      groups.exit().select("path").remove();

      groups.exit().transition().duration(1000)
        .style("opacity", 0).remove();

      var chords = container.selectAll("path.chord")
        .data(matrix.chords(), function (d) { return d._id; });

      chords.enter().append("path")
        .attr("class", "chord")
        .style("fill", function (d) {
          return colors(d.source._id);
        })
        .attr("d", path)
        .on("mouseover", chordMouseover)
        .on("mouseout", hideTooltip);

      chords.transition().duration(2000)
        .attrTween("d", matrix.chordTween(path));

      chords.exit().remove()

      function groupClick(d) {
        d3.event.preventDefault();
        d3.event.stopPropagation();
        $scope.addFilter(d._id);
        resetChords();
      }

      function chordMouseover(d) {
        d3.event.preventDefault();
        d3.event.stopPropagation();
        dimChords(d);
        d3.select("#tooltip").style("opacity", 1);
        $scope.updateTooltip(matrix.read(d));
      }

      function hideTooltip() {
        d3.event.preventDefault();
        d3.event.stopPropagation();
        d3.select("#tooltip").style("opacity", 0);
        resetChords();
      }

      function resetChords() {
        d3.event.preventDefault();
        d3.event.stopPropagation();
        container.selectAll("path.chord").style("opacity",0.9);
      }

      function dimChords(d) {
        d3.event.preventDefault();
        d3.event.stopPropagation();
        container.selectAll("path.chord").style("opacity", function (p) {
          if (d.source) { // COMPARE CHORD IDS
            return (p._id === d._id) ? 0.9: 0.1;
          } else { // COMPARE GROUP IDS
            return (p.source._id === d._id || p.target._id === d._id) ? 0.9: 0.1;
          }
        });
      }
    }; // END DRAWCHORDS FUNCTION

    function resize() {
      var width = $el.parent()[0].clientWidth;
      svg.attr({
        width: width,
        height: width / (size[0] / size[1])
      });
    }

    resize();

    $window.addEventListener("resize", function () {
      resize();
    });
  }; // END LINK FUNCTION

  return {
    link: link,
    restrict: 'EA'
  };

}]);


angular.module('userDash').factory('matrixFactory', [function () {

  var chordMatrix = function () {

    var _matrix = [], dataStore = [], _id = 0;
    var matrixIndex = [], indexHash = {};
    var chordLayout, layoutCache;

    var filter = function () {};
    var reduce = function () {};

    var matrix = {};

    matrix.update = function () {
      _matrix = [], objs = [], entry = {};

      layoutCache = {groups: {}, chords: {}};

      this.groups().forEach(function (group) {
        layoutCache.groups[group._id] = {
          startAngle: group.startAngle,
          endAngle: group.endAngle
        };
      });

      this.chords().forEach(function (chord) {
        layoutCache.chords[chordID(chord)] = {
          source: {
            _id: chord.source._id,
            startAngle: chord.source.startAngle,
            endAngle: chord.source.endAngle
          },
          target: {
            _id: chord.target._id,
            startAngle: chord.target.startAngle,
            endAngle: chord.target.endAngle
          }
        };
      });

      matrixIndex = Object.keys(indexHash);

      for (var i = 0; i < matrixIndex.length; i++) {
        if (!_matrix[i]) {
          _matrix[i] = [];
        }
        for (var j = 0; j < matrixIndex.length; j++) {
          objs = dataStore.filter(function (obj) {
            return filter(obj, indexHash[matrixIndex[i]], indexHash[matrixIndex[j]]);
          });
          entry = reduce(objs, indexHash[matrixIndex[i]], indexHash[matrixIndex[j]]);
          entry.valueOf = function () { return +this.value };
          _matrix[i][j] = entry;
        }
      }
      chordLayout.matrix(_matrix);
      return _matrix;
    };

    matrix.data = function (data) {
      dataStore = data;
      return this;
    };

    matrix.filter = function (func) {
      filter = func;
      return this;
    };

    matrix.reduce = function (func) {
      reduce = func;
      return this;
    };

    matrix.layout = function (d3_chordLayout) {
      chordLayout = d3_chordLayout;
      return this;
    };

    matrix.groups = function () {
      return chordLayout.groups().map(function (group) {
        group._id = matrixIndex[group.index];
        return group;
      });
    };

    matrix.chords = function () {
      return chordLayout.chords().map(function (chord) {
        chord._id = chordID(chord);
        chord.source._id = matrixIndex[chord.source.index];
        chord.target._id = matrixIndex[chord.target.index];
        return chord;
      });
    };

    matrix.addKey = function (key, data) {
      if (!indexHash[key]) {
        indexHash[key] = {name: key, data: data || {}};
      }
    };

    matrix.addKeys = function (props, fun) {
      for (var i = 0; i < dataStore.length; i++) {
        for (var j = 0; j < props.length; j++) {
          this.addKey(dataStore[i][props[j]], fun ? fun(dataStore[i], props[j]):{});
        }
      }
      return this;
    };

    matrix.resetKeys = function () {
      indexHash = {};
      return this;
    };

    function chordID(d) {
      var s = matrixIndex[d.source.index];
      var t = matrixIndex[d.target.index];
      return (s < t) ? s + "__" + t: t + "__" + s;
    }

    matrix.groupTween = function (d3_arc) {
      return function (d, i) {
        var tween;
        var cached = layoutCache.groups[d._id];

        if (cached) {
          tween = d3.interpolateObject(cached, d);
        } else {
          tween = d3.interpolateObject({
            startAngle:d.startAngle,
            endAngle:d.startAngle
          }, d);
        }

        return function (t) {
          return d3_arc(tween(t));
        };
      };
    };

    matrix.chordTween = function (d3_path) {
      return function (d, i) {
        var tween, groups;
        var cached = layoutCache.chords[d._id];

        if (cached) {
          if (d.source._id !== cached.source._id){
            cached = {source: cached.target, target: cached.source};
          }
          tween = d3.interpolateObject(cached, d);
        } else {
          if (layoutCache.groups) {
            groups = [];
            for (var key in layoutCache.groups) {
              cached = layoutCache.groups[key];
              if (cached._id === d.source._id || cached._id === d.target._id) {
                groups.push(cached);
              }
            }
            if (groups.length > 0) {
              cached = {source: groups[0], target: groups[1] || groups[0]};
              if (d.source._id !== cached.source._id) {
                cached = {source: cached.target, target: cached.source};
              }
            } else {
              cached = d;
            }
          } else {
            cached = d;
          }

          tween = d3.interpolateObject({
            source: {
              startAngle: cached.source.startAngle,
              endAngle: cached.source.startAngle
            },
            target: {
              startAngle: cached.target.startAngle,
              endAngle: cached.target.startAngle
            }
          }, d);
        }

        return function (t) {
          return d3_path(tween(t));
        };
      };
    };

    matrix.read = function (d) {
      var g, m = {};

      if (d.source) {
        m.sname  = d.source._id;
        m.sdata  = d.source.value;
        m.svalue = +d.source.value;
        m.stotal = _matrix[d.source.index].reduce(function (k, n) { return k + n; }, 0);
        m.tname  = d.target._id;
        m.tdata  = d.target.value;
        m.tvalue = +d.target.value;
        m.ttotal = _matrix[d.target.index].reduce(function (k, n) { return k + n; }, 0);
      } else {
        g = indexHash[d._id];
        m.gname  = g.name;
        m.gdata  = g.data;
        m.gvalue = d.value;
      }
      m.mtotal = _matrix.reduce(function (m1, n1) {
        return m1 + n1.reduce(function (m2, n2) { return m2 + n2; }, 0);
      }, 0);
      return m;
    };

    return matrix;
  };

  return {
    chordMatrix: chordMatrix
  };
}]);
