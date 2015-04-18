$(document).ready(function() {

  // automatically show selected tab 
  // if URL includes tab identifier after #
  // credit: http://stackoverflow.com/questions/7862233/twitter-bootstrap-tabs-go-to-specific-tab-on-page-reload
  if (location.hash) {
    var hash = location.hash
      , hashPieces = hash.split('?')
      , activeTab = $('[href=' + hashPieces[0] + ']');
    activeTab && activeTab.tab('show');
  }

});
