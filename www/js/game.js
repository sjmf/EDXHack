window.Game = {};
var G = window.Game;

window.Game.size = {
    width: window.innerWidth || document.body.clientWidth,
    height: window.innerHeight || document.body.clientHeight
}
var size = window.Game.size;

window.Game.game = new Phaser.Game(size.width, size.height, Phaser.AUTO, 'game');
var game = window.Game.game;

window.Game.PhaserGame = function () {

    // just adding comment to test
    this.bmd = null;

    // Path storage
    this.num_lanes = 4;
    this.lane_y_points = [];
    this.enemy_paths = {};
    this.x_bounds = [ (size.width-25), 25 ];

    // Enemy storage
    this.num_initial_enemies = 4;
    this.enemies = [];
    this.enemy_speed = 0.1;

    // Player storage
    this.num_items = [ 0, 0, 0 ];
    this.items = [];
    this.item_mode = 0;

    // Perfect city storage
    this.cities = [];

    // Engine stuff
    this.previous_time = 0;
    this.current_time = 0;

    this.printed = 0;
};
var PhaserGame = window.Game.PhaserGame;

window.Game.PhaserGame.prototype = {

    init: function () {

        this.game.renderer.renderSession.roundPixels = true;

    },

    preload: function () {

        //  We need this because the assets are on Amazon S3
        //  Remove the next 2 lines if running locally
        //this.load.baseURL = 'http://files.phaser.io.s3.amazonaws.com/codingtips/issue008/';
        //this.load.crossOrigin = 'anonymous';

        // Add enemy images
        this.load.image('PollutionCloud', 'assets/Pol/1.png');
       	this.load.image('Noise', 'assets/noise/5.png');
		
		// Add defense images
		this.load.image('GasMask', 'assets/gas/gas.png');
		this.load.image('SpeedLimit', 'assets/sign/sign.png');
        
		// Add city images
        this.load.image('newc', 'assets/back/newcastle.png');
        this.load.image('lond', 'assets/back/londonphoto.png');
        this.load.image('live', 'assets/back/liverpool.jpg');
        this.load.image('manc', 'assets/back/manchester.jpg');
        cities = [ 'newc', 'lond', 'live', 'manc' ];

        // Load background image
        this.load.image('background', 'assets/back/grass.png');
    },

    create: function () {

        this.bmd = this.add.bitmapData(this.game.width, this.game.height);
        this.bmd.addToWorld();

        //
        this.background = this.add.tileSprite(0, 0, game.width, game.height, 'background');
        this.background.fixedToCamera = true;

        // -----------
        // Setup Enemy paths
        // -----------
        this.num_lanes = 4;
        var path_interval = game.height / (this.num_lanes + 1);
        console.log(path_interval);

        // Generate enemy y values
        for (var i = 0; i < this.num_lanes; i++)
        {
            this.lane_y_points[i] = (path_interval * i) + path_interval;
            console.log(this.lane_y_points[i]);

            // Setup path's perfect city
            this.cities[i] = this.add.sprite(this.x_bounds[1], this.lane_y_points[i], cities[i]);
        }

        // ----------
        // Setup initial enemies
        // ----------
        var midpoint = game.width /2 ;
        for (var i = 0; i < this.num_initial_enemies; i++)
        {
            this.createEnemy('PollutionCloud', i);
        }

        // 
        this.genPaths();

        // ----------
        // Setup input
        // ----------
        this.game.input.onDown.add(this.placeItems, this);

        this.printed = 0;
    },

    createEnemy: function(type, lane) {

        this.enemies.push(
			this.add.sprite(this.x_bounds[0], this.lane_y_points[lane], type)
		);
		this.enemies[ this.enemies.length - 1 ].scale.set(4.0);
        this.enemies[ this.enemies.length - 1 ].anchor.set(0.5);
		switch (type)
        {
            case "NoisePollution":
                this.enemies[ this.enemies.length - 1 ].health = 50;
                this.enemies[ this.enemies.length - 1 ].dmg = 1;
                break;
            case "PollutionCloud":
                this.enemies[ this.enemies.length - 1 ].health = 50;
                this.enemies[ this.enemies.length - 1 ].dmg = 2;
                break;
        }
    },

    placeItems: function() {
        var x = this.game.input.activePointer.x;
        var y = this.game.input.activePointer.y;

        // Calculate distances between the lanes and the click to determine which lane was clicked
        var minDistance = Number.MAX_VALUE;
        var minIndex = 0;
        for (var i = 0; i < this.num_lanes; i++) 
        {
            var distance = Math.abs(y - this.lane_y_points[i]);
            if (distance < minDistance)
            {
                minDistance = distance;
                minIndex = i;
            }
        }


        // Set it's properties based on current item mode
        switch (this.item_mode)
        {
            case 0:
                this.items.push(this.add.sprite(x, this.lane_y_points[minIndex], 'GasMask'));
				this.items[ this.items.length - 1 ].perm = 0;
                break;
            case 1:
                this.items.push(this.add.sprite(x, this.lane_y_points[minIndex], 'GarbageBin'));
                this.items[ this.items.length - 1 ].perm = 1;
                break;
        }

		this.items[ this.items.length - 1 ].scale.set(4.0);
		this.items[ this.items.length - 1 ].anchor.set(0.5);
		this.items[ this.items.length - 1 ].dmg = 10;
		this.items[ this.items.length - 1 ].timer = 0;

        console.log(x + " " + y + " at lane y " + this.lane_y_points[minIndex]);
    },

    genPaths: function () {

        this.bmd.clear();

        this.enemy_paths= {};

        var x = 1 / game.width;

        // Generate points for each path
        for (var i = 0; i < this.num_lanes; i++)
        {
            // Create new array
            this.enemy_paths[i] = [];

            // Generate ponts
            for (var j = 0; j <= 1; j += x)
            {
                var px = this.math.linearInterpolation(this.x_bounds, j);
                var py = this.lane_y_points[i];

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

        // -------------
        // Update enemy movement
        // -------------
        for (var i = 0; i < this.enemies.length; i++)
        {
            if (this.enemies[i].x > this.x_bounds[1])
                this.enemies[i].x = this.enemies[i].x - (this.enemy_speed * dt);
            else
                this.enemies[i].x = this.x_bounds[0];

            // Collision detection with items
            for (var j = 0; j < this.items.length; j++)
            {
                // Check for overlap
                if (this.enemies[i].overlap(this.items[j]))
                {
                    // Account for permanent items and temporary items
                    if (this.items[j].perm == 0)
                    {
                        // temporary items are destroyed on collision
                        this.items[j].destroy(true);
                    }
                    this.enemies[i].damage(this.items[j].dmg);
                }
            }

            // Collision detection with cities
            for (var j = 0; j < this.cities.length; j++)
            {
                // Check for overlap
                if (this.enemies[i].overlap(this.cities[j]))
                {
                    this.cities[j].health = this.cities[j].health - this.enemies[i].dmg;
                    if (this.cities[j].health <= 0)
                    {
                        this.cities[j].exists = false;
                        this.cities[j].destroy(true);
                        G.util.achieve('Your city has been destroyed','danger');
                    }
                    this.enemies[i].destroy(true);
                }
            }
        }

        // Store previous time
        this.previous_time = this.current_time;
    }

};

game.state.add('Game', window.Game.PhaserGame, true);
