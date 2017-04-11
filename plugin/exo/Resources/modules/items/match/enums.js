
export const associationStyle = {
  strokeStyle: 'inherit', // delegates styles to less
  lineWidth: 6
}

// default jsPlumb configuration (connections are not changeable)
// for papers, feedback
export const jsPlumbDefaultConfig = {
  ConnectionsDetachable: false,
  Anchors: [
    'RightMiddle',
    'LeftMiddle'
  ],
  Connector: 'Straight',
  LogEnabled: false,
  PaintStyle: associationStyle
}

// jsPlumb configuration when enabled (connections are changeable)
// for player, editor
export const jsPlumbEnabledConfig = Object.assign({}, jsPlumbDefaultConfig, {
  ConnectionsDetachable: true,
  DropOptions: {
    tolerance: 'touch'
  }
})

// declare all association types and their corresponding CSS class
export const associationTypes = {
  selected: {
    cssClass: 'selected-association'
  },
  correct: {
    cssClass: 'correct-association'
  },
  incorrect: {
    cssClass: 'incorrect-association'
  },
  expected: {
    cssClass: 'expected-association'
  },
  unexpected: {
    cssClass: 'unexpected-association'
  },
  default: {
    cssClass: 'match-association'
  }
}
