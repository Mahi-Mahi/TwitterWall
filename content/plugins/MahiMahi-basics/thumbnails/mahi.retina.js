jQuery('img.retina').retina({
    // Check for data-retina attribute. If exists, swap out image
     dataRetina: true,
     // Suffix to append to image file name
     suffix: "@2x",
     // Check if image exists before swapping out
     checkIfImageExists: true,
     // Callback function if custom logic needs to be applied to image file name
     customFileNameCallback: "",
     // override window.devicePixelRatio
     overridePixelRation: true
});
