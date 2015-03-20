var landing = function()
{

}

landing.CleanUp =  function(cb)
{
  console.log("Cleaning for landing page");
  $('body').replaceWith($("<body></body>"));
  cb();
}