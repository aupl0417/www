/**
 * Created by Administrator on 2015/6/9.
 */
module.exports = function (grunt){
    grunt.initConfig({
        uglify: {
       	  buildall: {//按原文件结构压缩js文件夹内所有JS文件
                files: [{
                    expand:true,
                    cwd:'js',//js目录下
                    src:['*.js','!*.min.js'],//所有js文件
                    dest: 'js/dist',//输出到此目录下
                    ext: '.min.js'
                }]
            }
    },
   cssmin: {
		  target: {
		    files: [{
		      expand: true,
		      cwd: 'css',
		      src: ['*.css', '!*.min.css'],
		      dest: 'css/dist',
		      ext: '.min.css'
		    }]
		  }
	}
});
    // ¸æËßgruntÎÒÃÇ½«Ê¹ÓÃ²å¼þ
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    // ¸æËßgruntµ±ÎÒÃÇÔÚÖÕ¶ËÖÐÊäÈëgruntÊ±ÐèÒª×öÐ©Ê²Ã´
    grunt.registerTask('default', ['uglify','cssmin']);

};