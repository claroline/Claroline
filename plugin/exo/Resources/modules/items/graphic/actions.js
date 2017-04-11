import {makeActionCreator} from '#/main/core/utilities/redux'

export const SELECT_MODE = 'SELECT_MODE'
export const SELECT_IMAGE = 'SELECT_IMAGE'
export const RESIZE_IMAGE = 'RESIZE_IMAGE'
export const CREATE_AREA = 'CREATE_AREA'
export const SELECT_AREA = 'SELECT_AREA'
export const MOVE_AREA = 'MOVE_AREA'
export const DELETE_AREA = 'DELETE_AREA'
export const RESIZE_AREA = 'RESIZE_AREA'
export const TOGGLE_POPOVER = 'TOGGLE_POPOVER'
export const SET_AREA_COLOR = 'SET_AREA_COLOR'
export const SET_SOLUTION_PROPERTY = 'SET_SOLUTION_PROPERTY'
export const BLUR_AREAS = 'BLUR_AREAS'

export const actions = {}

actions.selectMode = makeActionCreator(SELECT_MODE, 'mode')
actions.selectImage = makeActionCreator(SELECT_IMAGE, 'image')
actions.resizeImage = makeActionCreator(RESIZE_IMAGE, 'width', 'height')
actions.createArea = makeActionCreator(CREATE_AREA, 'x', 'y')
actions.selectArea = makeActionCreator(SELECT_AREA, 'id')
actions.moveArea = makeActionCreator(MOVE_AREA, 'id', 'x', 'y')
actions.deleteArea = makeActionCreator(DELETE_AREA, 'id')
actions.resizeArea = makeActionCreator(RESIZE_AREA, 'id', 'position', 'x', 'y')
actions.togglePopover = makeActionCreator(TOGGLE_POPOVER, 'areaId', 'left', 'top', 'open')
actions.setAreaColor = makeActionCreator(SET_AREA_COLOR, 'id', 'color')
actions.setSolutionProperty = makeActionCreator(SET_SOLUTION_PROPERTY, 'id', 'property', 'value')
actions.blurAreas = makeActionCreator(BLUR_AREAS)
