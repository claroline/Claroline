import {resizeArea} from './resize'
import {ensure} from '#/main/core/tests'
import {
  SHAPE_RECT,
  SHAPE_CIRCLE,
  DIR_N,
  DIR_NE,
  DIR_E,
  DIR_SE,
  DIR_S,
  DIR_SW,
  DIR_W,
  DIR_NW
} from './enums'

describe('Resize function', () => {
  describe('on rectangular areas', () => {
    it('resizes from position N', () => {
      const resized = resizeArea({
        shape: SHAPE_RECT,
        coords: [
          {x: 20, y: 30},
          {x: 60, y: 50}
        ]
      }, DIR_N, 80, -20)
      ensure.equal(resized, {
        shape: SHAPE_RECT,
        coords: [
          {x: 20, y: 10},
          {x: 60, y: 50}
        ]
      })
    })
    it('does not overflow from position N', () => {
      const resized = resizeArea({
        shape: SHAPE_RECT,
        coords: [
          {x: 20, y: 50},
          {x: 90, y: 120}
        ]
      }, DIR_N, 30, 100)
      ensure.equal(resized, {
        shape: SHAPE_RECT,
        coords: [
          {x: 20, y: 119},
          {x: 90, y: 120}
        ]
      })
    })
    it('resizes from position S', () => {
      const resized = resizeArea({
        shape: SHAPE_RECT,
        coords: [
          {x: 20, y: 50},
          {x: 90, y: 120}
        ]
      }, DIR_S, 30, 60)
      ensure.equal(resized, {
        shape: SHAPE_RECT,
        coords: [
          {x: 20, y: 50},
          {x: 90, y: 180}
        ]
      })
    })
    it('does not overflow from position S', () => {
      const resized = resizeArea({
        shape: SHAPE_RECT,
        coords: [
          {x: 20, y: 50},
          {x: 90, y: 120}
        ]
      }, DIR_S, 30, -100)
      ensure.equal(resized, {
        shape: SHAPE_RECT,
        coords: [
          {x: 20, y: 50},
          {x: 90, y: 51}
        ]
      })
    })
    it('resizes from position W', () => {
      const resized = resizeArea({
        shape: SHAPE_RECT,
        coords: [
          {x: 20, y: 50},
          {x: 90, y: 120}
        ]
      }, DIR_W, -10, 50)
      ensure.equal(resized, {
        shape: SHAPE_RECT,
        coords: [
          {x: 10, y: 50},
          {x: 90, y: 120}
        ]
      })
    })
    it('does not overflow from position W', () => {
      const resized = resizeArea({
        shape: SHAPE_RECT,
        coords: [
          {x: 20, y: 50},
          {x: 90, y: 120}
        ]
      }, DIR_W, 100, 40)
      ensure.equal(resized, {
        shape: SHAPE_RECT,
        coords: [
          {x: 89, y: 50},
          {x: 90, y: 120}
        ]
      })
    })
    it('resizes from position E', () => {
      const resized = resizeArea({
        shape: SHAPE_RECT,
        coords: [
          {x: 20, y: 50},
          {x: 90, y: 120}
        ]
      }, DIR_E, 30, 50)
      ensure.equal(resized, {
        shape: SHAPE_RECT,
        coords: [
          {x: 20, y: 50},
          {x: 120, y: 120}
        ]
      })
    })
    it('does not overflow from position E', () => {
      const resized = resizeArea({
        shape: SHAPE_RECT,
        coords: [
          {x: 20, y: 50},
          {x: 90, y: 120}
        ]
      }, DIR_E, -80, 40)
      ensure.equal(resized, {
        shape: SHAPE_RECT,
        coords: [
          {x: 20, y: 50},
          {x: 21, y: 120}
        ]
      })
    })
    it('resizes from position NW', () => {
      const resized = resizeArea({
        shape: SHAPE_RECT,
        coords: [
          {x: 20, y: 30},
          {x: 60, y: 50}
        ]
      }, DIR_NW, -10, -20)
      ensure.equal(resized, {
        shape: SHAPE_RECT,
        coords: [
          {x: 10, y: 10},
          {x: 60, y: 50}
        ]
      })
    })
    it('does not overflow from position NW', () => {
      const resized = resizeArea({
        shape: SHAPE_RECT,
        coords: [
          {x: 20, y: 50},
          {x: 90, y: 120}
        ]
      }, DIR_NW, 100, 100)
      ensure.equal(resized, {
        shape: SHAPE_RECT,
        coords: [
          {x: 89, y: 119},
          {x: 90, y: 120}
        ]
      })
    })
    it('resizes from position NE', () => {
      const resized = resizeArea({
        shape: SHAPE_RECT,
        coords: [
          {x: 20, y: 30},
          {x: 60, y: 50}
        ]
      }, DIR_NE, 20, -10)
      ensure.equal(resized, {
        shape: SHAPE_RECT,
        coords: [
          {x: 20, y: 20},
          {x: 80, y: 50}
        ]
      })
    })
    it('does not overflow from position NE', () => {
      const resized = resizeArea({
        shape: SHAPE_RECT,
        coords: [
          {x: 20, y: 50},
          {x: 90, y: 120}
        ]
      }, DIR_NE, -80, 90)
      ensure.equal(resized, {
        shape: SHAPE_RECT,
        coords: [
          {x: 20, y: 119},
          {x: 21, y: 120}
        ]
      })
    })
    it('resizes from position SE', () => {
      const resized = resizeArea({
        shape: SHAPE_RECT,
        coords: [
          {x: 20, y: 30},
          {x: 60, y: 50}
        ]
      }, DIR_SE, 20, 40)
      ensure.equal(resized, {
        shape: SHAPE_RECT,
        coords: [
          {x: 20, y: 30},
          {x: 80, y: 90}
        ]
      })
    })
    it('does not overflow from position SE', () => {
      const resized = resizeArea({
        shape: SHAPE_RECT,
        coords: [
          {x: 20, y: 50},
          {x: 90, y: 120}
        ]
      }, DIR_SE, -80, -90)
      ensure.equal(resized, {
        shape: SHAPE_RECT,
        coords: [
          {x: 20, y: 50},
          {x: 21, y: 51}
        ]
      })
    })
    it('resizes from position SW', () => {
      const resized = resizeArea({
        shape: SHAPE_RECT,
        coords: [
          {x: 20, y: 30},
          {x: 60, y: 50}
        ]
      }, DIR_SW, -10, 10)
      ensure.equal(resized, {
        shape: SHAPE_RECT,
        coords: [
          {x: 10, y: 30},
          {x: 60, y: 60}
        ]
      })
    })
    it('does not overflow from position SW', () => {
      const resized = resizeArea({
        shape: SHAPE_RECT,
        coords: [
          {x: 20, y: 50},
          {x: 90, y: 120}
        ]
      }, DIR_SW, 90, -120)
      ensure.equal(resized, {
        shape: SHAPE_RECT,
        coords: [
          {x: 89, y: 50},
          {x: 90, y: 51}
        ]
      })
    })
  })

  describe('on circular areas', () => {
    it('grows the radius', () => {
      const resized = resizeArea({
        shape: SHAPE_CIRCLE,
        center: {x: 30, y: 70},
        radius: 50
      }, DIR_SW, -40, 60)
      ensure.equal(resized, {
        shape: SHAPE_CIRCLE,
        center: {x: 30, y: 70},
        radius: 110
      })
    })
    it('shrinks the radius', () => {
      const resized = resizeArea({
        shape: SHAPE_CIRCLE,
        center: {x: 30, y: 70},
        radius: 50
      }, DIR_E, -10, 5)
      ensure.equal(resized, {
        shape: SHAPE_CIRCLE,
        center: {x: 30, y: 70},
        radius: 40
      })
    })
    it('does not overflow the center', () => {
      const resized = resizeArea({
        shape: SHAPE_CIRCLE,
        center: {x: 30, y: 70},
        radius: 50
      }, DIR_N, 5, 60)
      ensure.equal(resized, {
        shape: SHAPE_CIRCLE,
        center: {x: 30, y: 70},
        radius: 1
      })
    })
  })
})
