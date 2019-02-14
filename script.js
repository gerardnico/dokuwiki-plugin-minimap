// Minimap

var minimap = {
    minilist: function () {
        console.log("Mini list started");
        $pages = jQuery("#minimap__plugin  .list-group-item")
        $pages.map(a => console.log($pages[a].innerText));

    }
}


window.addEventListener("load", function() {
    minimap.minilist();
});
