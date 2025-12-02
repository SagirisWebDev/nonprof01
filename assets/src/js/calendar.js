/* Style children of the TEC calendar iframe */
const body = document.querySelector( "body" );
if ( body.classList.contains( "page-id-316" ) ) {
  document.getElementById('tec-calendar-main').onload = function() {
      var iframeDoc = this.contentWindow.document;
      var element = iframeDoc.querySelector('html');
      if (element) {
          element.style.backgroundImage = 'none';
      }
  };
}