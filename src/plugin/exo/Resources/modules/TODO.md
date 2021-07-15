
# General
- [ ] remove all reference to translation function `tex()` use `trans('key', {}, 'quiz')` instead.
- [ ] move each quiz section (editor, player, etc.) in `#/plugin/exo/resources/quiz` while refactoring the section.
- [ ] rename `enums.js` in `constants.js` and only export a var `constants` which hold all the consts.
- [ ] replace all default exports with named ones.
- [ ] use new reducers syntax.
- [ ] clean selectors (remove duplicates + use reselect)
- [ ] add summary in player and papers (with a quiz param to enable/disable it)
- [ ] add progressbar in player (see Path player)
- [ ] make Items fully stand alone (to be able to reuse Editor, Player, Correction) in bank tool.
- [ ] merge `contents` and `items` (merge directories and defs, merge player).
- [ ] remove `-content` suffix in contents module names.
- [ ] clean translations.
- [ ] use new modal format.

# Evaluation
- [ ] implement `ResourceEvaluation`.

# Overview
- [ ] use new `ResourceOverview` core component.

# Player
- [ ] add routing for each step.
- [ ] manage end page route inside player module (for now in resource.jsx)
- [ ] add a button to delete a question answer (for now, there are some types for which you can not remove it, like unique choice)

# Editor
- [ ] add routing for each step.
- [ ] use standard form reducer and components

# Papers
- [ ] use ListData

# Items
- [ ] use correct app structure for items
