/* 
  Sub Menus for Home and Activities
*/
const homeMenu = document.querySelector(".home-submenu > ul");

const homeButton = document.querySelector(".home-submenu > a");

const activitiesMenu = document.querySelector(".activities-submenu > ul");

const activitiesButton = document.querySelector(".activities-submenu > a");

  // Disable anchor tags link
homeButton.setAttribute("href", "javascript:void(0)");
activitiesButton.setAttribute("href", "javascript:void(0)");


// Add hide class on page load
homeMenu.classList.add("hide");
activitiesMenu.classList.add("hide");

// Set Dropdown function
homeButton.addEventListener("click", () => {
  homeMenu.classList.toggle("hide");
})

activitiesButton.addEventListener("click", () => {
  activitiesMenu.classList.toggle("hide");
})


/* 
  Desktop Welcome Page Animations
*/
const body = document.querySelector('body');

if (window.innerWidth >= '1024' && body.classList.contains('home')) {

  // Welcome Page Title
  const containerDesktopLandingTitle = document.createElement('div');
  containerDesktopLandingTitle.id = 'desktop-title-container';

  containerDesktopLandingTitle.addEventListener('animationend', () => {
    containerDesktopLandingTitle.classList.add('animations-visible');
  })

  function addTitleClassesAndAppend(element) {
    element.classList.add('title-desktop-landing', 'text-shadow');
    containerDesktopLandingTitle.appendChild(element);
  }

  const desktopLandingTitle = document.createElement('h1');
  desktopLandingTitle.innerHTML = 'at <span>Spirit West United</span>';
  addTitleClassesAndAppend(desktopLandingTitle);

  const desktopLandingTitleChurch = document.createElement('h1');
  desktopLandingTitleChurch.innerHTML = '<span>Church</span>';
  addTitleClassesAndAppend(desktopLandingTitleChurch);

  // Links Under Header
  const noticesBanner = document.querySelector('.notices');
  noticesBanner.before(containerDesktopLandingTitle);

  // Adjust notices top padding for scrollIntoView
  noticesBanner.setAttribute('style', 'padding-top: 2rem')
  

  // Welcome Page Arrow
  const desktopLandingArrow = document.createElement('a');
  
  desktopLandingArrow.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="-18 -17 530 500" xml:space="preserve"><g><path fill="#dfdfdf" d="M52.8,311.3c-12.8-12.8-12.8-33.4,0-46.2c6.4-6.4,14.7-9.6,23.1-9.6s16.7,3.2,23.1,9.6l113.4,113.4V32.7   c0-18,14.6-32.7,32.7-32.7c18,0,32.7,14.6,32.7,32.7v345.8L391,265.1c12.8-12.8,33.4-12.8,46.2,0c12.8,12.8,12.8,33.4,0,46.2   L268.1,480.4c-6.1,6.1-14.4,9.6-23.1,9.6c-8.7,0-17-3.4-23.1-9.6L52.8,311.3z"/></g></svg>';

  desktopLandingArrow.classList.add('arrow-desktop-landing');

  containerDesktopLandingTitle.after(desktopLandingArrow);

  desktopLandingArrow.addEventListener('animationend', () => {
    desktopLandingArrow.classList.add('animations-visible');
  })
  
  // Smooth Scrolling for arrow button link
  desktopLandingArrow.addEventListener('click', (e) => {
    e.preventDefault;
    noticesBanner.scrollIntoView({behavior: "smooth", block: "start"});
  })

  const nav = document.querySelector('.header-desktop-landing');
  nav.style.backgroundColor = 'rgba(0, 0, 0, 0)';

  // Header background colour change on Welcome page
  window.addEventListener('scroll', function () {
    const scrollTrigger = window.innerHeight; // distance in pixels
  
    if (window.scrollY >= scrollTrigger) {
      nav.style.backgroundColor = 'rgb(255, 255, 251)';
    } else {
      nav.style.backgroundColor = 'rgba(255, 255, 251, 0)';
    }
  });
}