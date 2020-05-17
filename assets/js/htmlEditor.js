let tinymceScript = document.createElement('script');
tinymceScript.type = 'text/javascript';
tinymceScript.async = false;
tinymceScript.referrerpolicy = 'origin';

/*

FIX ME

DYNAMIC API KEY!



 */

tinymceScript.src = 'https://cdn.tiny.cloud/1/5j7e43rripdv3l4i30u28602hqqohvnvoaspxn29rroulhm0/tinymce/5/tinymce.min.js';

document.head.appendChild(tinymceScript);

tinymceScript.onload = function() {
    tinyMCE.init({
        selector: '#track_descriptionEn', /* FIXME */
        toolbar: 'undo redo bold italic',
        statusbar: true,
        menubar: false
    });
}
