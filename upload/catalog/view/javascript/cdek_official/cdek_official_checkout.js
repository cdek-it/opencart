console.log('sdf')
$('body').on('click', 'input', function (event) {
    console.log('sdfsss')
    if ($(event.target).val() == 'cdek_official.cdek_official_office_138') {
        $(event.target).after('<button class=\"cdek_pvz_map_btn\" id=\"cdek_official_office_button_138\">Выбрать ПВЗ</button>')
    }
})