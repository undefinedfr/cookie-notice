/*
 * 2017 Undefined
 * @author Nicolas Riviere
 */
module.exports = function(grunt) {

    // Import des modules
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.initConfig({
        // Get params from package.json
        pkg: grunt.file.readJSON('package.json'),
        paths: {
            sassDir : "assets/sass/",
            cssDir : "assets/css/",
            jsDir : "assets/js/",
            distDir : "assets/dist/"
        },

        // Compilation du sass
        sass: {
            dev: {
                options: {
                    style: 'compressed',
                },
                files: [{
                    "expand": true,
                    "cwd": "<%= paths.sassDir %>",
                    "src": ["**/*.scss"],
                    "dest": "<%= paths.cssDir %>",
                    "ext": ".css"
                }]
            }
        },

        // Compression des fichiers JavaScript
        uglify: {
            options: {
                separator: ';'
            },
            scripts: {
                files: [{
                    "expand": true,
                    "cwd": "<%= paths.distDir %>",
                    "src": ['**/*.js'],
                    "dest": "<%= paths.distDir %>"
                }]
            }
        },

        // Concatene les fichiers entre eux
        concat: {
            options: {
                separator: ';'
            },
            scripts: {
                src: ['<%= paths.jsDir %>**/*.js'],
                dest: '<%= paths.distDir %>undfnd-cookie-notice.js'
            }
        },

        // Watch les fichiers JS + Sass
        watch: {
            scripts: {
                files: ['<%= paths.jsDir %>**/*.js'],
                tasks: ['concat', 'uglify']
            },
            styles: {
                files: '<%= paths.sassDir %>**/*.scss',
                tasks: ['sass:dev']
            },

        }
    });

    // Tâche pour watch + concatenation JS Lib
    grunt.registerTask('default', ['watch']);

    grunt.loadNpmTasks('grunt-simple-watch');

};
