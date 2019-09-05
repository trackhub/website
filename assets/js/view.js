document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('elevation-container');
    const ctx = document.getElementById('elevation').getContext('2d');
    const myChart = new window.chartJs(ctx, {
        type: 'line',
        data: {
            labels: JSON.parse(container.dataset.labels),
            datasets: JSON.parse(container.dataset.datasets),
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: false
                    }
                }]
            },
            tooltips: {
                mode: 'nearest'
            }
        }
    });

    document.getElementById('ratingAlert').style.display = 'none';

    jQuery('#ratingModal').on('show.bs.modal', function (event) {
        const button = jQuery(event.relatedTarget);

        /* Get data from twig */
        const version = button.data('version');
        let path = button.data('path');
        path = path.replace("id", version);

        jQuery('#sendRating').on('click', function() {
            const rating = document.getElementById('ratingSelect').value;
            let but = this;

            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm m-1" role="status" aria-hidden="true"></span>';
            this.innerHTML += 'Sending...';

            jQuery.ajax({
                type: "POST",
                url: path,
                data: {
                    rating: rating
                },
                success: function(data) {
                    const rating = data.rating;
                    const votes = data.votes;

                    /* Update star rating */
                    const starRating = new StarRating("#rating-" + version);
                    starRating.setRating(rating, votes);
                    starRating.setTooltip('Rating: ' + rating + '<br>Votes: ' + votes);

                    /* Display 'thank you' message and close modal */
                    jQuery('#ratingAlert').html("" +
                        '<h4 class="alert-heading">Thank you!</h4>' +
                        '<p>You\'re make this site even better.</p>' +
                        "");
                    jQuery('#ratingAlert').addClass('alert-success').show(100);
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    let msg = JSON.parse(xhr.responseText)['message'];

                    jQuery('#ratingAlert').html("" +
                        '<h4 class="alert-heading">Ooops!</h4>' +
                        '<p>Something went wrong.</p>' +
                        '<hr>' +
                        '<p class="mb-0">' + msg + '.</p>' +
                        "");
                    jQuery('#ratingAlert').addClass('alert-danger').show(100);
                }
            }).done(function(){
                but.innerText = "Done";
            });
        });
    });

    jQuery('#ratingModal').on('hide.bs.modal', function () {
        jQuery('#sendRating').off('click');
        jQuery('#ratingAlert')
            .removeClass('alert-success')
            .removeClass('alert-danger')
            .hide();
        jQuery('#sendRating')
            .prop('disabled', false)
            .text('Rate');
    });
});

window.jsImageUpload = function (inputElement, csrfToken, uploadUrl, translations) {
    inputElement.addEventListener('change', function() {
        for (let i = 0; i < this.files.length; i++) {
            let previewDiv = document.createElement('div');
            previewDiv.classList.add('col');
            let previewImg = document.createElement('img');

            let fileReader = new FileReader();
            fileReader.onload = function() {
                let output = previewImg;
                output.src = fileReader.result;
            };
            fileReader.readAsDataURL(this.files[i]);

            previewDiv.appendChild(previewImg);
            document.getElementById('file_upload_progress').appendChild(previewDiv);

            let progressText = document.createElement('div');
            progressText.classList.add('upload_progress_text');
            progressText.innerText = 'pending';
            previewDiv.appendChild(progressText);

            if (this.files[i].size > 32 * 1024 * 1024) {
                progressText.innerText = 'File is too big';

                return;
            }

            let formData = new FormData();
            formData.append('file', this.files[i]);
            formData.append('token', csrfToken)
            let xhr = new XMLHttpRequest();

            xhr.upload.addEventListener('progress', function(e) {
                const percent_complete = ((e.loaded / e.total)*100).toFixed(1);
                progressText.innerText = percent_complete.toString() + '%';
            });

            xhr.addEventListener('load', function(e) {
                let responseObject = JSON.parse(xhr.response);
                if (responseObject.status === 0) {
                    progressText.innerText = translations.uploaded;
                } else {
                    progressText.innerText = responseObject.error;
                }
            });

            xhr.open('POST', uploadUrl, true);
            xhr.send(formData);
        }
    });
};
