const statusMessage = document.getElementById('status-message');

document.getElementById('image-upload-form').addEventListener('submit', function(event) {
    event.preventDefault();

    const url = document.getElementById('image-url').value;
    const formData = new FormData();
    formData.append('url', url);

    statusMessage.textContent = 'Uploading images...';

    fetch('/upload-images', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            updateGallery(data.images);
            statusMessage.textContent = 'Images uploaded successfully!';
        })
        .catch(error => {
            statusMessage.textContent = 'Error uploading images.';
            console.error('Error:', error);
        });
});

function updateGallery(images) {
    const gallery = document.getElementById('image-gallery');
    gallery.innerHTML = '';
    images.forEach(image => {
        const img = document.createElement('img');
        img.src = image.src;
        img.alt = image.alt;
        gallery.appendChild(img);
    });
}

window.onload = function() {
    fetch('/get-saved-images')
        .then(response => response.json())
        .then(data => updateGallery(data.images))
        .catch(error => {
            console.error('Error:', error);
        });
};
