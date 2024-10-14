/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

$(document).on('click', '#play-button', function () {
    document.getElementById('poster-img').style.display = 'none';
    document.getElementById('play-button').style.display = 'none';
    document.getElementById('youtube-video').style.display = 'block';
    document.getElementById("video-iframe").src += "&autoplay=1";
});

$(document).on('click', '.detail-movie', function () {
    let id = $(this).attr('data-id');
    let title = $(this).attr('data-title');
    let vote_avg = $(this).attr('data-voteavg');
    let vote_cont = $(this).attr('data-votecount');
    let url = Routing.generate('movie_details', {'id': id});
    $.ajax({
        url: url,
        method: "GET",
        cache: false,
        async: false,
        success: function (data) {
            $("#video-modal").attr('src', 'https://www.youtube.com/embed/' + data.detailMovie.link + '?si=0M8phrjjyzmQk5yA');
            $('#title-video-modal').text(title + ', ' + data.detailMovie.name);
            $('#desc-movie-modal').text('Film : ' + title + ' ' + data.detailMovie.name);
            let vote = Math.floor((vote_avg / 2) * 10) / 10;
            $('#rating-movie').html('<div class="rating" style="--rating: ' + vote + ';"></div> ' + vote + ' pour ' + vote_cont + ' utilisateurs');
            $("#modal-video").modal({show: true});
        }
    });
});

$(".genre-check-input").click(function (e, parameters) {
    let selectedGenres = [];
    let checkboxes = document.querySelectorAll('.genre-check-input');
    checkboxes.forEach(function (checkbox) {
        if (checkbox.checked) {
            selectedGenres.push(checkbox.value);
        }
    });
    let queryParams = 'genres=' + selectedGenres.join(',');
    let newUrl = '?' + queryParams;
    window.history.replaceState({}, '', newUrl);
    location.reload();
});

const findMovies = () => {
    let query = $('#search-movie').val();
    let url = Routing.generate('movie_autocomplete', {'query': query});
    fetch(url)
        .then(response => {
            return response.json();
        })
        .then(data => {
            displayMovies(data);
        });
}

function displayMovies(data) {
    let movies = [];
    data.results.forEach(result => movies.push(result.original_title));
    let uniqueMovies = [...new Set(movies)];
    let html = uniqueMovies.map(movie => {
        return `
                      <li>
                        <a href="?search=${movie}"><span class="movie">${movie}</span></a>
                      </li>
                    `;
    }).join('');
    suggestions.innerHTML = html;
    suggestions.style.display = 'block';
}

let titleInput = document.getElementById("search-movie");
let suggestions = document.querySelector('.suggestions');

if (titleInput) {
    titleInput.addEventListener("keyup", findMovies);
}