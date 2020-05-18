let tinymceScript = document.createElement('script');
tinymceScript.type = 'text/javascript';
tinymceScript.async = false;
tinymceScript.referrerpolicy = 'origin';

// API KEY should be dynamic,
// but I don't think there is an easy way to make it dynamic
tinymceScript.src = 'https://cdn.tiny.cloud/1/5j7e43rripdv3l4i30u28602hqqohvnvoaspxn29rroulhm0/tinymce/5/tinymce.min.js';

document.head.appendChild(tinymceScript);

tinymceScript.onload = function() {
    tinyMCE.init({
        selector: '[data-html="wysiwyg"]',
        toolbar: 'undo redo bold italic link numlist bullist',
        plugins: 'lists link',
        statusbar: true,
        menubar: false
    });
}
