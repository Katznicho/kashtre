window.addEventListener('upload-success', event => {
    Swal.fire({
        title: 'Success!',
        text: `${event.detail.count} items were uploaded successfully.`,
        icon: 'success',
        confirmButtonText: 'OK'
    });
});

window.addEventListener('upload-failed', event => {
    const errors = event.detail.errors;
    const errorList = errors.join('\n');
    
    Swal.fire({
        title: 'Upload Failed',
        html: `<div class="text-left"><p>${event.detail.message}</p><pre class="mt-2 text-sm text-red-600">${errorList}</pre></div>`,
        icon: 'error',
        confirmButtonText: 'OK'
    });
});
