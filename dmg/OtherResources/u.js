var currentVersion = "v2008.12.09";

var req;
var updateURL = 'http://www.tekl.de/deutsch/OpenThesaurus_Deutsch.html';

window.setTimeout("loadXMLDoc(updateURL)", 500);

function loadXMLDoc(url) {
   try {
      req = new XMLHttpRequest();
   } catch(e) {
      req = false;
   }
   if(req) {
      req.onreadystatechange = processReqChange;
      req.open("GET", url, true);
      req.send("");
   }
}

function processReqChange() {
   // only if req shows "loaded"
   if (req.readyState == 4) {
      // only if "OK"
      if (req.status == 200) {
         newestVersion = req.responseText.match(/v\d\d\d\d.\d\d.\d\d/);
         if (newestVersion != null) {
            newestVersion = newestVersion.toString();
            if (newestVersion > currentVersion) {
               result = '<a class="newVersion" href="'+updateURL+'"><img src="Images/update.gif" valign="middle" alt=""/></a> <a class="newVersion" href="'+updateURL+'">Neue Version verfügbar!</a>';
            } else {
               result = '';
            }
         } else {
            result = '';
         }
      } else {
         result = '';
      }
      if (result != '') { document.getElementById("c").innerHTML = result; }
   }
}
