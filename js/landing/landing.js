var landing = function()
{

}

landing.CleanUp =  function(cb)
{
  console.log("Cleaning for landing page");
  $('body').replaceWith($("<body></body>"));
  cb();

  landing.PatchMenuCall();
}

landing.PatchMenuCall = function()
{
  phoxy.MenuCall = function(url)
  {
    if (typeof url != 'string')
    {
      url = args.shift();
      url += "(" + phoxy.Serialize(args) + ")";
    }
    phoxy.Reset(url);
  }
}