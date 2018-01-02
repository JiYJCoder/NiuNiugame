let Xtip = function (opts) {
    this.opts = $.extend({}, Xtip.default, opts)
    this.el = $('.x-tip')
    this.$main = $('<div class="x-tip-main"></div>')
}

Xtip.default = {
    background: '#fff',
    color: '#333',
    borderRadius: 5,
    padding: '5px 10px',
    fontSize: 12,
    top: -27
}

Xtip.prototype.create = function () {
    let opts = this.opt
    let el = this.el
    let el_length = el.length
    let el_main = this.$main

    el.css({
        position: 'relative',
        textDecoration: 'underline',
        cursor: 'pointer'
    })

    el_main.css({
        position: 'absolute',
        top: opts.top,
        left: '50%',
        padding: opts.padding,
        borderRadius: opts.borderRadius,
        background: opts.background,
        textAlign: 'center',
        color: opts.color,
        whiteSpace: 'nowrap',
        display: 'none'
    })

    for (let i = 0; i < el_length; i++) {
        let _this = el.eq(i)
        let data = _this.data('xtip')
        let el_clone = el_main.clone()
        _this.append(el_clone.html(data))
        let el_margin_left = - el_clone.outerWidth() / 2
        let el_height = el_clone.outerHeight()
        el_clone.css('margin-left', el_margin_left)
        el_clone.append('<span></span>')
        el_clone.find('span').css({
            position: 'absolute',
            top: el_height - 1,
            left: '50%',
            width: 0,
            height: 0,
            borderLeft: '6px solid transparent',
            borderRight: '6px solid transparent',
            borderTop: '6px solid #fff',
            marginLeft: '-6px'
        })
    }

    el.on('mouseenter', function(e) {
        $(this).find('.x-tip-main').show()
    })
    el.on('mouseleave', function(e) {
        $(this).find('.x-tip-main').hide()
    })

}
