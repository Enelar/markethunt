var 
  system = require('system'),
  model, output, size;
var handler = system.args[1];

if (system.args.length < 3)
  phantom.exit();

id = system.args[1];
file = system.args[2];
code = system.args[3];

var page = require('webpage').create();
page.settings.loadImages = false;
page.settings.localToRemoteUrlAccessEnabled = true;
page.settings.resourceTimeout = 3000; // 15 seconds
page.settings.userAgent = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/537.36';
page.onResourceTimeout = function(e) {
  console.log('timeout');
  phantom.exit(1);
};

phantom.addCookie({
  'name'     : 'yandex_gid',   /* required property */
  'value'    : 2,  /* required property */
  'domain'   : 'market.yandex.ru',
  'path'     : '/',                /* required property */
  'expires'  : (new Date()).getTime() + (1000 * 60 * 60)   /* <-- expires in 1 hour */
});

var export_flag = false;
var result = null;
function wait_for_flag()
{
  if (!export_flag)
    return setTimeout(arguments.callee, 100);
  exportf(result);
}
wait_for_flag();

function set_flag(res)
{
  result = res;
  export_flag = true;
}

var exportf = function(ret)
{
  var fs = require('fs');
  fs.write(file, JSON.stringify(ret), 'w');  
  phantom.exit();
};

var url = "http://localhost:8080/api/spider/phantomjs/Show?id=" + id;


page.onResourceError = function(resourceError) {
    page.reason = resourceError.errorString;
    page.reason_url = resourceError.url;
};

page.open(url, function (status) 
{
  if (status !== 'success')
  {
    console.log('LOAD_FAIL');
    console.log(page.reason);
    console.log(page.reason_url);
    console.log(status);
    console.log(JSON.stringify(page));
    phantom.exit();
  }
  else 
  {     
    page.includeJs("http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js", 
      function() 
      {
        //console.log(page.content.length);
        console.log(code);
        var res = page.evaluate(function(code)
          {
            var ret = null;
            eval(code);
            return ret;
          }, code);
        console.log(res);
        set_flag(res)
      });
  }
});  
