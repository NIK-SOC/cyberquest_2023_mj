package main

import (
	"strconv"
)

func cdNormal(key string) bool {
	if len(key) == 11 {
		firstSegment, err1 := strconv.Atoi(key[:3])
		lastSegment, err2 := strconv.Atoi(key[4:])
		if err1 == nil && err2 == nil {
			switch firstSegment {
			case 333, 444, 555, 666, 777, 888, 999:
				return false
			default:
				return key[3] == '-' && checkMod7(lastSegment, 7)
			}
		}
	}

	return false
}

func getNthDigitFromEnd(number, index int) int {
	divisor := 1
	for i := 0; i < index; i++ {
		divisor *= 10
	}
	return (number / divisor) % 10
}

func checkMod7(segment, length int) bool {
	sum := 0
	for i := 0; i < length; i++ {
		sum += getNthDigitFromEnd(segment, i)
	}
	return sum%7 == 0
}

// func main() {
// 	keys := []string{"123-45678901", "566-3568686", "743-3411822"}
// 	for _, key := range keys {
// 		if cdNormal(key) {
// 			fmt.Println(key + " is a valid CD key")
// 		} else {
// 			fmt.Println(key + " is not a valid CD key")
// 		}
// 	}
// }
