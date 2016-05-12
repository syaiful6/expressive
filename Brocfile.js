var pickFiles   = require('broccoli-funnel');
var compileLess = require('broccoli-less-single');
var mergeTrees  = require('broccoli-merge-trees');

var app = pickFiles('static', {
  srcDir:  '/',
  destDir: '/'
});

var less = compileLess(app, 'less/app.less', 'css/app.css', {
  paths: ['.', 'node_modules/bootstrap-less/bootstrap']
})

module.exports = mergeTrees([less]);
