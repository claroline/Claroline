# Reducers

## Prototype des fonctions

`makeReducer(initialState, handlers)`
* makeReducer spécifique du dataForm
`makeFormReducer(formName, initialState, customReducer)`
* makeReducer specifique du listForm
`makeListReducer(listName, initialState = {}, customReducer = {}, options = {})`

## Exemple de création d'un initialState dans un formReducer

```
form: makeFormReducer('subjects.form', {
  showSubjectForm: false
}, {
  showSubjectForm: makeReducer(false, {})
})
```
