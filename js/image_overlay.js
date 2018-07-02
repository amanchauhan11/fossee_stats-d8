(function ($, Drupal, drupalSettings) {
        Drupal.behaviors.fossee_stats = {
            attach: function (context, settings) { 
                image_urls = [];
                for(let i = 0; i < drupalSettings.image_urls.length; i++)
                    image_urls.push(drupalSettings.image_urls[i]);
                old_urls = image_urls;
                $("#previous-image").once("fossee_stats").click(shiftLeft);
                $("#next-image").once("fossee_stats").click(shiftRight);
            }
        };

}(jQuery, Drupal, drupalSettings));

function openNav(link) {
    document.getElementById("myNav").style.width = "100%";
    for(let i = 0; image_urls[0] != old_urls[link-1]; i++)
        shiftLeft();
}
function closeNav() {
    document.getElementById("myNav").style.width = "0%";
}

function reasignUrl() {
    document.getElementById("overlay-image").src = image_urls[0];
}
function shiftLeft() {
    let first_url = image_urls[0];
    image_urls = image_urls.slice(1,image_urls.length).reverse();
    image_urls.unshift(first_url);
    image_urls = image_urls.reverse();
    reasignUrl();
}
function shiftRight() {
    let last_url = image_urls[image_urls.length-1];
    image_urls = image_urls.slice(0,image_urls.length-1).reverse();
    image_urls.push(last_url);
    image_urls = image_urls.reverse();
    reasignUrl();
}