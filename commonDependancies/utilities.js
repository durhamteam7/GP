var utilitiesModule = angular.module('utilities',[]);

/**
 * Gets number of keys of an object
 *
 * @memberof utilitiesModule
 * @ngdoc filter
 * @name keylength
 * @param {Object} input
 * @returns {number} Number of keys in the object
 * @desc
 *  Exposes Object.keys() to angular
 */
utilitiesModule.filter('keylength', function(){
  return function(input){
    if(!angular.isObject(input)){
      return 0;
    }
    return Object.keys(input).length;
  };
});

/**
 * Gets field name only
 *
 * @memberof utilitiesModule
 * @ngdoc filter
 * @name removeTableName
 * @param {String} input path in dot notation
 * @returns {String} field name
 *
 */
utilitiesModule.filter('removeTableName', function(){
  return function(input){
    if (input == undefined)
    {
      return "";
    }
    var stringSplit = input.split(".");
    return stringSplit[stringSplit.length-1];
  };
});

/**
 * Angular's LimitTo for Objects
 *
 * @memberof utilitiesModule
 * @ngdoc filter
 * @name keylength
 * @param {Object} obj
 * @param {number} limit The number of keys to limit the output to
 * @returns {Object} the first limit keys of the input object
 */
utilitiesModule.filter('objectLimitTo', [function(){
    return function(obj, limit){
    	if(!angular.isObject(obj))
    	{
    		return [];
    	}
        var keys = Object.keys(obj);
        if(keys.length < 1){
            return [];
        }

        var ret = {};
        count = 0;
        angular.forEach(keys, function(key, arrayIndex){
           if(count >= limit){
                return false;
            }
            ret[key] = obj[key];
            count++;
        });
        return ret;
    };
}]);

/**
 * Removes new line characters
 *
 * @memberof utilitiesModule
 * @ngdoc filter
 * @name newlines
 * @param {String} text
 * @returns {String} text without new lines
 */
utilitiesModule.filter('newlines', function () {
    return function(text) {
        return text.replace(/\n/g, '');
    };
});

/**
 * Converts an object to an array ordered by a field
 *
 * @memberof utilitiesModule
 * @ngdoc filter
 * @name orderObjectBy
 * @param {Object} items
 * @param {String} field A property of each object in items to sort by
 * @param {boolean} [reverse] true to reverse order
 * @returns {Object[]} An array of the object properties ordered by field
 */
utilitiesModule.filter('orderObjectBy', function() {
  return function(items, field, reverse) {
    var filtered = [];
    angular.forEach(items, function(item,key) {
      item.id=key;
      filtered.push(item);
    });
    filtered.sort(function (a, b) {
      return (a[field] > b[field] ? 1 : -1);
    });
    if(reverse) filtered.reverse();
    return filtered;
  };
});


utilitiesModule.directive('validNumber', function() {
  return {
    require: '?ngModel',
    link: function(scope, element, attrs, ngModelCtrl) {
      if(!ngModelCtrl) {
        return;
      }

      ngModelCtrl.$parsers.push(function(val) {
        if (angular.isUndefined(val)) {
            var val = '';
        }
        var clean = val.replace( /[^0-9]+/g, '');
        if (val !== clean) {
          ngModelCtrl.$setViewValue(clean);
          ngModelCtrl.$render();
        }
        return clean;
      });

      element.bind('keypress', function(event) {
        if(event.keyCode === 32) {
          event.preventDefault();
        }
      });
    }
  };
});
