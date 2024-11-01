function onload(event) {
    ratingData.ratingDataArray.forEach(function (ratingDataArray) {
        // console.log(ratingDataArray);
        var averageRating = ratingDataArray.averageRating;
        var ratingId = ratingDataArray.ratingId;
        var star = ratingDataArray.star;
        var font = ratingDataArray.font;
        if(star == 1){
        $rating = averageRating/5;
        }else{
            $rating = averageRating*1;
        }
        var increment = ratingDataArray.increment;
         increment = raterJs({
            max: star,
            starSize: font,
            rating: parseFloat($rating),
            readOnly:true,
            showToolTip:false,
            element: document.querySelector("#" + ratingId),
            rateCallback: function rateCallback(rating, done) {
                this.setRating(rating);
                done();
            }
        });
    });
}

window.addEventListener("load", onload, false);