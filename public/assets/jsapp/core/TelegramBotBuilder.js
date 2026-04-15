export class TelegramBotBuilder {
    constructor() {
        this.blocks = [];
        this.currentId = 1;
    }

    createBlock(text, buttons = [], linkedFrom = null, randomn) {
        const block = { id: this.currentId++, text, buttons, linkedFrom ,randomn};
        this.blocks.push(block);
        return block;
    }

    findBlock(id) {
        return this.blocks.find(b => b.randomn === String(id));
    }

    findLinkedBlocks(parentId) {
        return this.blocks.filter(b => b.linkedFrom === parentId);
    }

    updateBlock(id, text, buttons) {
        const block = this.findBlock(id);
        if (block) {
            block.text = text;
            block.buttons = buttons;
        }
    }

    deleteBlock(id) {
        this.blocks = this.blocks.filter(b => b.randomn !== String(id) && b.linkedFrom !== String(id));

    }

    getBlocks() {
        return this.blocks;
    }
}
