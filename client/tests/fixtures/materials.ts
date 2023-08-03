import type { Material } from '@/stores/api/materials';

const materials: Material[] = [
    {
        id: 31,
        category_id: 1,
        name: 'Material B',
        reference: 'MatB',
        description: null,
        sub_category_id: null,
        out_of_order_quantity: 0,
        is_hidden_on_bill: false,
        is_reservable: true,
        tags: [],
        attributes: [],
        created_at: '2022-05-15 14:30:00',
        updated_at: '2022-05-15 14:30:00',
        park_id: 1,
        stock_quantity: 2,
        rental_price: 10,
        replacement_price: 350,
        is_discountable: true,
    },
    {
        id: 32,
        category_id: 1,
        name: 'Material A',
        reference: 'MatA',
        description: null,
        sub_category_id: null,
        out_of_order_quantity: 0,
        is_hidden_on_bill: false,
        is_reservable: true,
        tags: [],
        attributes: [],
        created_at: '2022-05-15 14:30:00',
        updated_at: '2022-05-15 14:30:00',
        park_id: 1,
        stock_quantity: 1,
        rental_price: 10,
        replacement_price: 125,
        is_discountable: true,
    },
    {
        id: 33,
        category_id: 2,
        name: 'Material D',
        reference: 'MatD',
        description: null,
        sub_category_id: null,
        out_of_order_quantity: 0,
        is_hidden_on_bill: false,
        is_reservable: true,
        tags: [],
        attributes: [],
        created_at: '2022-05-15 14:30:00',
        updated_at: '2022-05-15 14:30:00',
        park_id: 1,
        stock_quantity: 112,
        rental_price: 5,
        replacement_price: 150,
        is_discountable: true,
    },
    {
        id: 34,
        category_id: 3,
        name: 'Material C',
        reference: 'MatC',
        description: null,
        sub_category_id: null,
        out_of_order_quantity: 0,
        is_hidden_on_bill: false,
        is_reservable: true,
        tags: [],
        attributes: [],
        created_at: '2022-05-15 14:30:00',
        updated_at: '2022-05-15 14:30:00',
        park_id: 2,
        stock_quantity: 1,
        rental_price: 15,
        replacement_price: 200,
        is_discountable: false,
    },
    {
        id: 35,
        category_id: 2,
        name: 'Material E',
        reference: 'MatE',
        description: null,
        sub_category_id: null,
        out_of_order_quantity: 0,
        is_hidden_on_bill: false,
        is_reservable: true,
        tags: [],
        attributes: [],
        created_at: '2022-05-15 14:30:00',
        updated_at: '2022-05-15 14:30:00',
        park_id: 2,
        stock_quantity: 3,
        rental_price: 45,
        replacement_price: 650,
        is_discountable: true,
    },
];

export default materials;
