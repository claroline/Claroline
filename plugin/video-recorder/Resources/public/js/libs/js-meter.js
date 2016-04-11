/**
 * extracted from nexus ui library http://nexusosc.com/
 */
export default class Meter {

  constructor(options) {

    this.options = {
      "fill": typeof options !== 'undefined' && typeof options.fill !== 'undefined' ? options.fill : "#eeeeee",
      "element": typeof options !== 'undefined' && typeof options.element !== 'undefined' ? options.element : "db-meter",
      "bars": typeof options !== 'undefined' && typeof options.bars !== 'undefined' ? options.bars : 8
    };


    // keep canvas request animation frame id to be able to cancel it
    this.rafID = null;
    this.canvas = document.getElementById(this.options.element);
    this.context = this.canvas.getContext('2d');
    this.dataArray;
    this.bar = {
      x: 0,
      y: 0,
      w: this.canvas.width,
      h: this.canvas.height / this.options.bars
    }

    this.context.fillStyle = this.options.fill;
    this.context.fillRect(0, 0, this.canvas.width, this.canvas.height);
  }


  setup(actx, source) {
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

  draw() {

    if (this.dataArray) {
      this.analyser.getByteTimeDomainData(this.dataArray);

      var max = Math.max.apply(null, this.dataArray);
      var min = Math.min.apply(null, this.dataArray);
      var amp = max - min;
      amp /= 240

      //converts amps to db
      var db = 20 * (Math.log(amp) / Math.log(10))
      this.context.fillStyle = this.options.fill;
      this.context.fillRect(0, 0, this.canvas.width, this.canvas.height);

      //scales: -40 to +10 db range => a number of bars
      var dboffset = Math.floor((db + 40) / (50 / this.options.bars));

      for (var i = 0; i < this.options.bars; i++) {

        // 0+ db is red
        if (i >= this.options.bars * .8) {
          this.context.fillStyle = '#ff0000';

          // -5 to 0 db is yellow
        } else if (i < this.options.bars * .8 && i >= this.options.bars * .69) {
          this.context.fillStyle = '#ffff00';
          // -40 to -5 db is green
        } else if (i < this.options.bars * .69) {
          this.context.fillStyle = '#10ff00';
        }
        // draw bar
        // if (i < dboffset){ -> original conditions ... but with this conditions we never go to red nor yellow colors
        if (i <= dboffset + 1) {
          //  this.context.fillRect(1, this.canvas.height - this.bar.h * i, this.canvas.width - 2, this.bar.h - 2);
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
