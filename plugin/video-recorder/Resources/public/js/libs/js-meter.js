
/**
* extracted from nexus ui library http://nexusosc.com/
*/
export default class Meter{

  constructor(){

    this.colors = {
      "accent": "#ff5500",
      "fill": "#eeeeee",
      "border": "#e3e3e3",
      "mid": "#1af",
      "black": "#000000",
      "white": "#FFFFFF"
    };

    this.rafID = null;
    this.canvas = document.getElementById("meter");
    this.context = this.canvas.getContext('2d');
    this.dataArray;
    this.bars = 8;
    this.bar = {
      x: 0,
      y: 0,
      w: this.canvas.width,
      h: this.canvas.height / this.bars
    }

    this.context.fillStyle = this.colors.fill;
    this.context.fillRect(0, 0, this.canvas.width, this.canvas.height);
  }


  setup (actx, source) {
    this.actx = actx;
    this.source = source;
    this.analyser = this.actx.createAnalyser();
    this.analyser.smoothingTimeConstant = 0.85;
    this.analyser.fftsize = 1024;
    this.bufferLength = this.analyser.frequencyBinCount;
    this.dataArray = new Uint8Array(this.bufferLength);
    this.source.connect(this.analyser);

    this.draw();
  }

  draw(){

    if (this.dataArray) {
      this.analyser.getByteTimeDomainData(this.dataArray);

      var max = Math.max.apply(null, this.dataArray);
      var min = Math.min.apply(null, this.dataArray);
      var amp = max - min;
      amp /= 240

      //converts amps to db
      var db = 20 * (Math.log(amp) / Math.log(10))
      this.context.fillStyle = this.colors.fill;
      this.context.fillRect(0, 0, this.canvas.width, this.canvas.height);

      //scales: -40 to +10 db range => a number of bars
      var dboffset = Math.floor((db + 40) / (50 / this.bars));

      for (var i = 0; i < this.bars; i++) {

        // 0+ db is red
        if (i >= this.bars * .8) {
          this.context.fillStyle = '#ff0000';

          // -5 to 0 db is yellow
        } else if (i < this.bars * .8 && i >= this.bars * .69) {
          this.context.fillStyle = '#ffff00';
          // -40 to -5 db is green
        } else if (i < this.bars * .69) {
          this.context.fillStyle = '#10ff00';
        }
        // draw bar
        if (i <= dboffset + 1){
            this.context.fillRect(1, this.canvas.height - this.bar.h * i, this.canvas.width - 2, this.bar.h - 2);
        }
      }
    }
    window.cancelAnimationFrame(this.rafID);
    setTimeout(function() {
      this.rafID = window.requestAnimationFrame(this.draw.bind(this));
    }.bind(this), 80)
  }

}
