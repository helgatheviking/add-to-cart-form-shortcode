/**
 * Build automation scripts.
 *
 * @package WooCommerce Mix and Match
 */

module.exports = function(grunt) {

	require( 'load-grunt-tasks' )( grunt );

	// Project configuration.
	grunt.initConfig(
		{
			pkg: grunt.file.readJSON( 'package.json' ),

			// # Build and release

			// Remove any files in zip destination and build folder.
			clean: {
				main: ['build/**']
			},

			// Copy the plugin into the build directory.
			copy: {
				main: {
					src: [
					'**',
					'!node_modules/**',
					'!build/**',
					'!deploy/**',
					'!svn/**',
					'!**/*.zip',
					'!**/*.bak',
					'!wp-assets/**',
					'!package-lock.json',
					'!nyp-logo.png',
					'!screenshots/**',
					'!.git/**',
					'!**.md',
					'!Gruntfile.js',
					'!package.json',
					'!gitcreds.json',
					'!.gitcreds',
					'!.gitignore',
					'!.gitmodules',
					'!.code-workspace',
					'!sftp-config.json',
					'!**.sublime-workspace',
					'!**.code-workspace',
					'!**.sublime-project',
					'!deploy.sh',
					'!**/*~',
					'!phpcs.xml',
					'!composer.json',
					'!composer.lock',
					'!vendor/**',
					'!none',
					'!.nvmrc',
					'!.jshintrc',
					'!.distignore',
					'!**/*.scss',
					'!assets//scss/**'
					],
					dest: 'build/'
				}
			},

			// Make a zipfile.
			compress: {
				main: {
					options: {
						mode: 'zip',
						archive: 'deploy/<%= pkg.version %>/<%= pkg.name %>.zip'
					},
					expand: true,
					cwd: 'build/',
					src: ['**/*'],
					dest: '/<%= pkg.name %>'
				}
			},

			// Bump version numbers (replace with version in package.json).
			replace: {
				version: {
					src: [
					'readme.txt',
					'<%= pkg.name %>.php'
					],
					overwrite: true,
					replacements: [
					{
						from: /Stable tag:.*$/m,
						to: "Stable tag: <%= pkg.version %>"
					},
					{
						from: /Version:.*$/m,
						to: "Version: <%= pkg.version %>"
					},
					{
						from: /public \$version = \'.*.'/m,
						to: "public $version = '<%= pkg.version %>'"
					},
					{
						from: /public \$version      = \'.*.'/m,
						to: "public $version      = '<%= pkg.version %>'"
					}
					]
				}
			}

		}
	);

	// Register tasks.
	grunt.registerTask(
		'zip',
		[
		'clean',
		'copy',
		'compress'
		]
	);

	grunt.registerTask( 'release', [ 'replace', 'zip', 'clean' ] );

};
