class EnemyVisualModifierTruesight extends EnemyVisualModifier {
    constructor(enemyvisual, index) {
        super(enemyvisual, index);
        // If it's loaded already, set it now
        this.iconName = this.enemyvisual.enemy.npc !== null && this.enemyvisual.enemy.npc.truesight === 1 ? 'truesight' : '';
    }

    _getValidIconNames() {
        return [
            '', // we are allowed to have nothing
            'truesight',
        ];
    }

    _getTemplateData(width, height, margin) {
        console.assert(this instanceof EnemyVisualModifierTruesight, 'this is not an EnemyVisualModifierTruesight!', this);

        // Top left corner
        return {
            classes: 'modifier_external ' + this.iconName,
            left: -8, // 16px wide; divided by 2
            top: 0
        };
    }
}