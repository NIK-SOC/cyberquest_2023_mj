use js_sys::Math;
use wasm_bindgen::prelude::*;

const IS_TRUSTZONE_AVAILABLE: bool = false;
const TRUSTZONE_API: *const u8 = 0x00000000 as *const u8;
const UID_API: *const u8 = 0x80000000 as *const u8;

fn random_within_range(start: usize, end: usize) -> usize {
    (Math::random() * (end - start) as f64).floor() as usize + start
}

fn seven_div_generator(length: usize) -> String {
    let mut num_array: Vec<usize> = Vec::with_capacity(length);

    for _ in 0..length - 1 {
        num_array.push(random_within_range(0, 9));
    }

    num_array.push(random_within_range(1, 7));

    while num_array.iter().sum::<usize>() % 7 != 0 {
        num_array[random_within_range(0, length - 1)] = random_within_range(0, 9);
    }

    num_array
        .iter()
        .map(|num| num.to_string())
        .collect::<String>()
}

#[wasm_bindgen]
pub fn gen_serial() -> String {
    if IS_TRUSTZONE_AVAILABLE {
        let raw_serial = unsafe { std::slice::from_raw_parts(TRUSTZONE_API, 16) };
        return String::from_utf8(raw_serial.to_vec()).unwrap();
    }
    let first_digits: usize;

    loop {
        let temp_num = random_within_range(0, 998);
        match temp_num {
            333 | 444 | 555 | 666 | 777 | 888 => (),
            _ => {
                first_digits = temp_num;
                break;
            }
        }
    }

    format!("{:0>3}-{}", first_digits, seven_div_generator(7))
}

#[wasm_bindgen]
pub fn sign(url: String, post_data: String) -> String {
    if IS_TRUSTZONE_AVAILABLE {
        if url.len() > 32 || post_data.len() > 32 {
            return "undefined".to_string();
        }
        let mut url_bytes = url.as_bytes().to_vec();
        let mut post_data_bytes = post_data.as_bytes().to_vec();
        url_bytes.append(&mut post_data_bytes);
        let raw_signature = unsafe { std::slice::from_raw_parts(TRUSTZONE_API, 32) };
        return String::from_utf8(raw_signature.to_vec()).unwrap();
    }
    "undefined".to_string()
}

#[wasm_bindgen]
pub fn get_uid() -> String {
    if UID_API != 0x80000000 as *const u8 {
        let raw_uid = unsafe { std::slice::from_raw_parts(UID_API, 8) };
        return String::from_utf8(raw_uid.to_vec()).unwrap();
    }
    "undefined".to_string()
}
