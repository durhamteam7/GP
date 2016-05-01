var serverCommsModule = angular.module('serverComms',[]);

//Allows for switching between dev local server and hosted server
var urls = ["http://localhost:8080/", "https://mammalweb.herokuapp.com/"];
var env = 1; // GLOBAL VARIABLE FOR ENVIRONMENT (0:Dev,1:Production)

/**
 * @memberof serverComms
 * @ngdoc factory
 * @name ajax
 * @param {service} http Allows requests to api
 * @return {object} functions
 * @description
 *   Makes http requests
 */
serverCommsModule.factory('ajax', ['$http', function($http) {
    return {
        getPhotos: function(query, pageNum, pageSize, isSequence) {
            return $http.post(urls[env] + 'photo?pageNum=' + pageNum + '&pageSize=' + pageSize + '&sequence=' + isSequence, query).success(function() {});
        },
        getFullPhotos: function(query, isSequence) {
            return $http.post(urls[env] + 'photo?sequence=' + isSequence, query).success(function() {});
        },
        getPhotosCSV: function(query, isSequence) {
            return $http.post(urls[env] + 'photo?output=csv&sequence=' + isSequence, query).success(function() {});
        },
        getOptions: function() {
            return $http.get(urls[env] + 'options').success(function() {});
        },
        getAlgorithmSettings: function() {
            return $http.get(urls[env] + 'algorithmSettings').success(function() {});
        },
        updateAlgorithmSettings: function(settings) {
            return $http.post(urls[env] + 'algorithmSettings', settings).success(function() {});
        },
        getPersons: function() {
            return $http.get(urls[env] + 'persons').success(function() {});
        },
        getFilters: function() {
            return $http.get('../commonDependancies/filters.json').success(function() {});
        },
        setFavourite: function(person_id,photo_id,isSet){
          body = {"person_id":person_id,"photo_id":photo_id,"isSet":isSet};
          return $http.post('../commonDependancies/setFavourite.json',body).success(function() {});
        }
    };
}]);
