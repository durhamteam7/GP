//Utilities for treating objects like arrays
adminApp.filter('keylength', function(){
  return function(input){
    if(!angular.isObject(input)){
      return 0;
    }
    return Object.keys(input).length;
  }
});

adminApp.filter('objectLimitTo', [function(){
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



adminApp.filter('newlines', function () {
    return function(text) {
        return text.replace(/\n/g, '');
    }
});

adminApp.filter('orderObjectBy', function() {
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
