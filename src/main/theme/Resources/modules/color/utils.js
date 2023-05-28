import {interpolateRainbow} from 'd3-scale-chromatic'

/**
 * FOR RETRO COMPATIBILITY ONLY. Do not use.
 */

const schemeCategory20c = []
for (let i = 0; i < 20; i++) {
  schemeCategory20c.push(interpolateRainbow(i / 20))
}

export {
  schemeCategory20c
}