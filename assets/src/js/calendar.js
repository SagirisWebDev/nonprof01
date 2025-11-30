/* Style children of the TEC calendar iframe */
document.getElementById('tec-calendar-main').onload = function() {
    var iframeDoc = this.contentWindow.document;
    var element = iframeDoc.querySelector('html');
    if (element) {
        element.style.backgroundImage = 'none';
    }
};