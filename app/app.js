var a = function() {
	this.test = function() {
		console.log("test")
	}
	return this;
}();


var x = a.test;
a.test = function() {
	console.log("testing")
	x();
}
// a.test();
