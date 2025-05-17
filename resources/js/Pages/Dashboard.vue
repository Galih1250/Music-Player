<template>
    <div id="bg">
        <div>
            <h1>All Songs</h1>

        <div>
            <input type="text" v-model="searchQuery" placeholder="Search by artist" />
            <button @click="filterSongs">| Search |</button>
            <button @click="searchInternet('apple')">| Search Apple Music |</button>
    <button @click="searchInternet('youtube')">| Search YouTube |</button>
    <button @click="searchInternet('spotify')">| Search Spotify |</button>
        </div>

            <div>
                <h1>My Music</h1>
                <div>
                    <button
                        v-for="(song, index) in songs"
                        :key="index"
                        :class="{ active: song === activeItem }"
                        @click="play(song)"
                        style="display: block; margin: 5px 0;">
                        <span>{{ "|" + song.title }} - {{ song.artist + "|" }}</span>
                        <button
                            @click.stop="deleteSong(song.id)"
                            class="delete-button"
                            title="Delete">
                            ✕|
                        </button>
                        <button
                            @click.stop="editSong(song)"
                            class="edit-button"
                            title="Edit">
                            ✎|
                        </button>
                    </button>
                </div>

                <br />

                <button type="button" @click.prevent="toggleForm">
                    {{ isFormOpen ? 'Cancel' : 'Add New Song' }}
                </button>
                <div v-if="isFormOpen">
                    <form @submit.prevent="submit">
                        <div>
                            <label>Title </label>
                            <input type="text" v-model="form.title" required />
                        </div>
                        <div>
                            <label>Artist </label>
                            <input type="text" v-model="form.artist" required />
                        </div>
                        <div>
                            <label>Song </label>
                            <input
                                name="src"
                                type="file"
                                accept="audio/*"
                                @change="uploadSong"
                                required
                            />
                        </div>
                        <div>
                            <label>Cover Image </label>
                            <input
                                name="cover"
                                type="file"
                                accept="image/*"
                                @change="uploadCover"
                            />
                        </div>
                        <div>
                            <button type="submit">{{ isEditing ? 'Update' : 'Submit' }}</button>
                        </div>
                    </form>
                </div>
            </div>

            <div>
                <br />
                <span>{{ currentSong.title }} - {{ currentSong.artist }}</span>
                <div>
                    <button type="button" @click="prev">| Previous |</button>
                    <button
                        type="button"
                        @click="isPlaying ? pause() : play(currentSong)"
                    >
                        {{ isPlaying ? "| Pause |" : "| Play |" }}
                    </button>
                    <button type="button" @click="next">| Next |</button>
                    <button type="button" @click= "searchLyrics">| Lyrics |</button>
                </div>
                <img :src="'/storage/' + currentSong.cover" alt="Cover Image" style="height: 250px; width: 250px;" />
            </div>
        </div>
    </div>
</template>

<script>
import { Inertia } from "@inertiajs/inertia";

export default {
    props: ["songs"],
    data() {
    return {
        audio: new Audio(),
        currentSong: {},
        index: 0,
        isPlaying: false,
        isFormOpen: false,
        isEditing: false,
        activeItem: null,
        searchQuery: "", 
        form: this.$inertia.form({
            title: "",
            artist: "",
            src: "",
            cover: "",
        }),
    };
},
    mounted() {
        this.currentSong = this.songs[this.index];
        this.activeItem = this.songs[this.index];
        this.audio.src = "/storage/" + this.currentSong.src;
        this.originalSongs = [...this.songs];
    },

    methods: {
    toggleForm() {
        this.isFormOpen = !this.isFormOpen;
        if (!this.isFormOpen) {
            this.form.reset();
            this.isEditing = false;
        }
    },
    editSong(song) {
        this.isEditing = true;
        this.form.title = song.title;
        this.form.artist = song.artist;
        this.form.src = song.src; 
        this.form.cover = song.cover; 
        this.isFormOpen = true;
    },
    uploadSong(e) {
        const file = e.target.files[0];
        const reader = new FileReader();
        reader.onloadend = () => {
            this.form.src = reader.result;
        };
        reader.readAsDataURL(file);
    },
    uploadCover(e) {
        const file = e.target.files[0];
        const reader = new FileReader();
        reader.onloadend = () => {
            this.form.cover = reader.result;
        };
        reader.readAsDataURL(file);
    },
    submit() {
        if (this.isEditing) {
            this.form.put(this.route("song.update", this.currentSong.id), {
                preserveScroll: true,
                onSuccess: () => {
                    this.form.reset();
                    this.isFormOpen = false;
                    this.isEditing = false;
                },
            });
        } else {
            this.form.post(this.route("song.store"), {
                preserveScroll: true,
                onSuccess: () => {
                    this.form.reset();
                    this.isFormOpen = false;
                },
            });
        }
    },
    play(song) {
        if (song) {
            this.currentSong = song;
            this.audio.src = "/storage/" + this.currentSong.src;
            this.audio.play();
            this.isPlaying = true;
            this.activeItem = this.currentSong;
        }
    },
    pause() {
        this.audio.pause();
        this.isPlaying = false;
    },
    prev() {
        this.index = (this.index - 1 + this.songs.length) % this.songs.length;
        this.currentSong = this.songs[this.index];
        this.play(this.currentSong);
    },
    next() {
        this.index = (this.index + 1) % this.songs.length;
        this.currentSong = this.songs[this.index];
        this.play(this.currentSong);
    },
    deleteSong(songId) {
        if (confirm("Are you sure you want to delete this song?")) {
            Inertia.delete(`/songs/${songId}`, {
                onSuccess: () => {
                    alert("Song deleted successfully.");
                },
                onError: (errors) => {
                    console.error("There was an error deleting the song:", errors);
                    alert("Failed to delete the song.");
                },
            });
        }
    },
    searchLyrics() {
        console.log("Current Song:", this.currentSong); 
        if (this.currentSong.title && this.currentSong.artist) {
            const title = encodeURIComponent(this.currentSong.title);
            const artist = encodeURIComponent(this.currentSong.artist);
            const searchUrl = `https://www.google.com/search?q=${title}+${artist}+lyrics`;
            window.open(searchUrl, '_blank'); 
        } else {
            alert("No song selected to search for lyrics.");
        }
    },
    filterSongs() {
        this.$inertia.get('/songs', { artist: this.searchQuery }, { preserveState: true });
    },
    searchInternet(platform) {
        if (this.searchQuery) {
            const query = encodeURIComponent(this.searchQuery);
            let searchUrl = '';
            
            switch (platform) {
                case 'apple':
                    searchUrl = `https://music.apple.com/search?term=${query}`;
                    break;
                case 'youtube':
                    searchUrl = `https://www.youtube.com/results?search_query=${query}`;
                    break;
                case 'spotify':
                    searchUrl = `https://open.spotify.com/search/${query}`;
                    break;
                default:
                    alert("Please select a valid platform.");
                    return;
            }
            window.open(searchUrl, '_blank');
        } else {
            alert("Please enter an artist name to search.");
        }
    },

}}

</script>