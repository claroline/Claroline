(function () {
    'use strict';

    describe('The utilities', function () {
        beforeEach(function () {
            this.utilities = Claroline.Utilities;
        });

        describe('#formatText method', function () {
            it('replaces exceeding text with dots', function () {
                expect(this.utilities.formatText('Lorem ipsum', 10, 1)).toBe('Lorem i...');
            });

            it('can split text into multiple lines', function () {
                expect(this.utilities.formatText('Loremipsumdolorsitamet', 8, 3))
                    .toBe('Loremips<br/>umdolors<br/>itamet');
            });

            it('avoids slicing words whenever possible', function () {
                expect(this.utilities.formatText('Lorem ipsum dolor sit amet', 7, 4))
                    .toBe('Lorem <br/>ipsum <br/>dolor <br/>sit ...');
            });
        });
    });
})();