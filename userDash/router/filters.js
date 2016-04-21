//Utilities for treating objects like arrays
userApp.filter('keylength', function(){
  return function(input){
    if(!angular.isObject(input)){
      return 0;
    }
    return Object.keys(input).length;
  }
});

userApp.filter('objectLimitTo', [function(){
    return function(obj, limit){
    	if(!angular.isObject(obj))
    	{
    		return [];
    	}
        var keys = Object.keys(obj);
        if(keys.length < 1){
            return [];
        }

        var ret = new Object,
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



userApp.filter('newlines', function () {
    return function(text) {
        return text.replace(/\n/g, '');
    }
});

userApp.filter('orderObjectBy', function() {
  return function(items, field, reverse) {
    var filtered = [];
    angular.forEach(items, function(item,key) {
      item['id']=key;
      filtered.push(item);
    });
    filtered.sort(function (a, b) {
      return (a[field] > b[field] ? 1 : -1);
    });
    if(reverse) filtered.reverse();
    return filtered;
  };
});
