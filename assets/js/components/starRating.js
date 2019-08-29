class StarRating {
    constructor(target, stars = 5) {
        this.target = target;
        this.stars = stars;
    }

    setRating(rating, votes) {
        if (votes == 0) {
            return;
        }

        const percentage = ((rating / votes) / this.stars) * 100;
        const percentageRounded = Math.round(percentage / 10) * 10 + '%';

        document.querySelector(this.target + ' .stars-inner').style.width = percentageRounded;
    }

    setTooltip(text) {
        document.querySelector(this.target).setAttribute('data-original-title', text);
    }
}

module.exports = StarRating;