var userApp = angular.module('userDash', ['rzModule', 'ui.bootstrap','googlechart',"checklist-model",'datetimepicker','ui.dashboard']);



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
	$("#loader").fadeOut("slow");

	$scope.id = "1";
}]);