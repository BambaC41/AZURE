FROM golang:1.26 AS builder

WORKDIR /app

COPY go.mod go.sum ./
RUN go mod download

COPY . .

RUN CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -o school-api .

FROM alpine:3.20

WORKDIR /app

COPY --from=builder /app/school-api .

EXPOSE 3000

CMD ["./school-api"]