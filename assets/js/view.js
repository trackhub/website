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