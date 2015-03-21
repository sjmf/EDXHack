var size = {
    width: window.innerWidth || document.body.clientWidth,
    height: window.innerHeight || document.body.clientHeight
}    

var game = new Phaser.Game(size.width, size.height, Phaser.AUTO, 'game');

var PhaserGame = function () {

    this.bmd = null;

    // Path storage
    this.num_paths = 4;
    this.enemy_y_points = [];
    this.enemy_paths = {};
    this.x_bounds = [ (size.width-25), 25 ];

    // Enemy storage
    this.num_enemies = 4;
    this.enemies = [];
    this.enemy_speed = 0.1;

    // Perfect city storage
    this.perfect_cities = [];

    // Engine stuff
    this.previous_time = 0;
    this.current_time = 0;

    this.pi = 0;
};

PhaserGame.prototype = {

    init: function () {

        this.game.renderer.renderSession.roundPixels = true;
        this.stage.backgroundColor = '#204090';

    },

    preload: function () {

        //  We need this because the assets are on Amazon S3
        //  Remove the next 2 lines if running locally
        //this.load.baseURL = 'http://files.phaser.io.s3.amazonaws.com/codingtips/issue008/';
        //this.load.crossOrigin = 'anonymous';

        // Add enemy images
        this.load.image('pollutionCloud', 'assets/Pol/1.png');
        
        // Add city images
        this.load.image('newc', 'assets/back/newcastle.png');
        this.load.image('lond', 'assets/back/london.png');
        cities = [ 'newc', 'lond' ];
    },

    create: function () {

        this.bmd = this.add.bitmapData(this.game.width, this.game.height);
        this.bmd.addToWorld();

        // -----------
        // Setup Enemy paths
        // -----------
        this.num_paths = 4;
        var path_interval = game.width / (this.num_paths + 1);

        // Generate enemy y values
        for (var i = 0; i < this.num_paths; i++)
        {
            this.enemy_y_points[i] = (path_interval * i) + path_interval;

            // Setup path's perfect city
            this.perfect_cities[i] = this.add.sprite(this.x_bounds[1], this.enemy_y_points[i], cities[i]);
        }

        // ----------
        // Setup initial enemies
        // ----------
        var midpoint = game.width /2 ;
        for (var i = 0; i < this.num_enemies; i++)
        {
            this.enemies[i] = this.add.sprite(this.x_bounds[0], this.enemy_y_points[i], 'pollutionCloud');
            this.enemies[i].anchor.set(0.5);
        }

        // 
        this.genPaths();
    },

    genPaths: function () {

        this.bmd.clear();

        this.enemy_paths= {};

        var x = 1 / game.width;

        // Generate points for each path
        for (var i = 0; i < this.num_paths; i++)
        {
            // Create new array
            this.enemy_paths[i] = [];

            // Generate ponts
            for (var j = 0; j <= 1; j += x)
            {
                var px = this.math.linearInterpolation(this.x_bounds, j);
                var py = this.enemy_y_points[i];

                this.enemy_paths[i].push( { x: px, y: py });

                // For drawing the white path
                //this.bmd.rect(px, py, 1, 1, 'rgba(255, 255, 255, 1)');
            }
        }
    },

    update: function () {

        // Get current time and calculate delta
        this.current_time = this.game.time.time;
        var dt = this.current_time - this.previous_time;

        // Update enemy paths
        for (var i = 0; i < this.num_enemies; i++)
        {
            if (this.enemies[i].x > this.x_bounds[1])
                this.enemies[i].x = this.enemies[i].x - (this.enemy_speed * dt);
            else
                this.enemies[i].x = this.x_bounds[0];
        }

        // 


        this.pi++;

        if (this.pi >= this.enemy_paths[0].length)
        {
            this.pi = 0;
        }

        // Store previous time
        this.previous_time = this.current_time;
    }

};

game.state.add('Game', PhaserGame, true);
